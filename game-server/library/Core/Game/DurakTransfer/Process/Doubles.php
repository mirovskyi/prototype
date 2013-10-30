<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 29.03.12
 * Time: 14:48
 *
 * Реализация розыгрыша в игре "Дурак переводной" (парная игра)
 */
class Core_Game_DurakTransfer_Process_Doubles extends Core_Game_DurakTransfer_Process
{

    /**
     * Проверка возможности пользователя(ей) подкинуть карту
     *
     * @param Core_Game_Durak_Players_Player|null $player Если указан игрок, проверяется возможность игроком подкинуть карты.
     * Если игрок не указан, проверяется вообще возможность подкидывать карты в розыгрыше
     * @param Core_Game_Durak_Cards_Card|null $card Карту которую необходимо добавить в розыгрыш
     * @param Exception|null $exception
     * @return bool
     */
    function canAddCard(Core_Game_Durak_Players_Player $player = null, Core_Game_Durak_Cards_Card $card = null, &$exception = null)
    {
        //Проверка достижения максимального количества карт на игровом столе
        if ($this->_maxCardCount() == count($this->_cards)) {
            $exception =  new Core_Game_Durak_Exception('Reached limit the number of cards in the hand',
                                                        3001, Core_Exception::USER);
            return false;
        }

        //Получаем количество не отбитых карт в розыгрыше
        $noBeatCards = 0;
        foreach($this->_cards as $c) {
            if (null === $c) {
                $noBeatCards ++;
            }
        }
        //Проверяем, достаточно ли карт у отбивающегося
        if (count($this->getDefender()->getCardArray()) <= $noBeatCards) {
            $exception = new Core_Game_Durak_Exception('Reached limit the number of cards in the hand',
                                                       3001, Core_Exception::USER);
            return false;
        }

        if (null === $player) {
            return true;
        }

        //Отбивающий не может подкидывать карты
        if ($this->getDefender() == $player) {
            $exception =  new Core_Game_Durak_Exception('Defender can\'t throw card',
                                                        0, Core_Exception::USER);
            return false;
        }

        //Проверка первого хода в розыгрыше
        if (!count($this->_cards)) {
            //Проверяем является ли игрок атакующим
            if ($this->getGame()->getPlayersContainer()->getAtackPlayer() != $player) {
                $exception =  new Core_Game_Durak_Exception('Player is not attacker',
                    3001, Core_Exception::USER);
                return false;
            } else {
                //Первый ход атакующего пользователя
                return true;
            }
        }

        //Проверяем не является ли указанный игрок партнером отбивающего
        if ($this->getDefender()->isPartner($player)) {
            $exception = new Core_Game_Durak_Exception('Can\'t go to the partner', 3002, Core_Exception::USER);
            return false;
        }

        //Проверка возможности пользователя подкинуть карты
        $cards = $player->getCardArray();
        foreach($this->_cards as $card1 => $card2) {
            if (null !== $card) {
                //Проверка наличичя старшинства добавляемой карты в розыгрыше
                if ($card->equalValue($card1) || $card->equalValue($card2)) {
                    return true;
                }
                continue;
            }
            //Проверка наличия у игрока карт для добавления в розыгрыш
            $cardValue1 = substr($card1, 1);
            $cardValue2 = substr($card2, 1);
            if ($cards->hasCardValue($cardValue1) || $cards->hasCardValue($cardValue2)) {
                return true;
            }
        }
        //У игрока нет подходящих карт
        $exception = new Core_Game_Durak_Exception('The player has no cards for the current drawing',
                                                   3008, Core_Exception::USER);
        return false;
    }

    /**
     * Обработка завершения розыгрыша
     *
     * @return bool Возвращает флаг завершения всей партии
     */
    public function finish()
    {
        //Проверяем отбился ли игрок
        if ($this->isDefend()) {
            //Карты розыгрыша в отбой
            $this->getGame()->getPulldown()->add($this->getCardArray());
            //Добавляем в истроию (анимацию) комманду "карты в отбой"
            $this->getGame()->getAnimation()->addAction($this->getGame()->getCommand(), Core_Game_Durak_Animation::CLEAR);
        } else {
            //Карты розыгрыша не отбившему игроку
            $this->getDefender()->getCardArray()->add($this->getCardArray());
            //Добавляем комманду взятия карт в историю (анимацию)
            //Получаем позицию отбивающегося игрока
            $position = $this->getDefender()->getId();
            $this->getGame()->getAnimation()->addAction($this->getGame()->getCommand(), Core_Game_Durak_Animation::TAKE, $position);
        }

        //Раздача карт игрокам
        $this->_dealCards();

        //Проверка наличия карт в колоде
        if (count($this->getGame()->getPack())) {
            //Еще есть карты в колоде, нет вышедших из игры. Партия не завершена
            //переключаем атакующего игрока
            $this->getGame()->getPlayersContainer()->switchActivePlayer($this->isDefend());
            return false;
        }

        //Проверка выигравшей пары
        $endGame = false;
        foreach($this->getGame()->getPlayersContainer() as $player) {
            //Убираем флаг активности игрока
            $player->setActive(false);
            //Проверка наличия карт у пользователя
            if ($this->_hasPlayerCards($player)) {
                continue;
            }
            //Карт нет, изменяем состояние игрока
            $player->setPlay(false);
            //Проверяем наличие карт у партнера
            $partner = $this->_getPartner($player);
            if (!$this->_hasPlayerCards($partner)) {
                //Карт нет ни у текущего игрока, ни у партнета
                //Устанавливаем игроку очки за выход
                $player->addPoints($this->getGame()->getPoints());
                //Установка флага завершения партии
                $endGame = true;
            }
        }
        //Если партия не закончена, переключаем атакующего игрока
        if (!$endGame) {
            $this->getGame()->getPlayersContainer()->switchActivePlayer($this->isDefend());
        } else {
            //Инкремент сыгранных партий
            $this->getGame()->incGamesPlay();
        }

        //Возвращаем флаг завершение партии
        return $endGame;
    }

    /**
     * Обработка достижения таймаута игроками(ом)
     *
     * @param Core_Game_Durak_Players_Player $player
     * @param bool                           $finishMatch
     *
     * @return void
     */
    public function handleTimeout(Core_Game_Durak_Players_Player $player, $finishMatch = false)
    {
        //Инкремент сыгранных партий
        $this->getGame()->incGamesPlay();

        //Флаг завершения матча
        $endMatch = false;
        //Если это первый розыгрыш в партии и игрок не сделал ни одного действия - завершение матча
        if ($finishMatch ||
                ($this->isFirstProccess() && count($player->getCardArray()) == Core_Game_Durak::PLAYER_CARDS_COUNT)) {
            //Завершение всего матча
            $endMatch = true;
            //Обнуление очков игрока и его партнера
            $player->setPoints(0);
            $this->_getPartner($player)->setPoints(0);
        }

        //Добавление очков соперникам за победу в партии
        foreach($this->getGame()->getPlayersContainer() as $element) {
            if (!$player->isPartner($element)) {
                $element->addPoints($this->getGame()->getPoints());
            }
        }

        //Окончание партии/матча
        $this->getGame()->finishGame($endMatch);
    }

    /**
     * Получение партнера игрока
     *
     * @param Core_Game_Durak_Players_Player $player
     * @return Core_Game_Durak_Players_Player
     */
    private function _getPartner(Core_Game_Durak_Players_Player $player)
    {
        //Клонируем объект итератора игроков, т.к. текущий метод может быть вызван внутри итерации и сбить курсор
        $iterator = clone($this->getGame()->getPlayersContainer()->getIterator());
        //Сбрасываем курсор
        $iterator->rewind();
        //Получение партнера
        foreach($iterator as $element) {
            if ($player->isPartner($element)) {
                return $this->getGame()->getPlayersContainer()->getPlayer($element->getSid());
            }
        }
    }

    /**
     * Проверка наличия карт на руках у игрока
     *
     * @param Core_Game_Durak_Players_Player $player
     * @return bool
     */
    private function _hasPlayerCards(Core_Game_Durak_Players_Player $player)
    {
        return count($player->getCardArray()) > 0;
    }
}
