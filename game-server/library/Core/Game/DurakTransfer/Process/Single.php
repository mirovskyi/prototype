<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.03.12
 * Time: 18:51
 *
 * Реализация розыгрыша в игре "Дурак переводной" (одиночная игра)
 */
class Core_Game_DurakTransfer_Process_Single extends Core_Game_DurakTransfer_Process
{

    /**
     * Проверка возможности пользователя(ей) подкинуть карту
     *
     * @abstract
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

        //Проверка возможности пользователя подкинуть карту(ы)
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
            $iterator = $this->getGame()->getPlayersContainer()->getIterator();
            $position = $iterator->getElementIndex($this->getDefender());
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

        //Сумма очков за выход в розыгрыше
        $pointsAmount = 0;
        //Получаем список игроков, которые учаcтвовали в розыгрыше и по окончании у них нет карт на руках
        $outPlayers = array();
        foreach($this->getGame()->getPlayersContainer() as $player) {
            if ($player->isPlay() && !count($player->getCardArray())) {
                $outPlayers[] = $player->getSid();
                //Сумируем очки за выход
                $pointsAmount += $this->getGame()->getPoints();
                //Уменьшаем очки за следующий выход
                $this->getGame()->setPoints($this->getGame()->getPoints() - 0.5);
            }
        }
        //Проверка наличия вышедших игроков в текущей партии
        if (count($outPlayers)) {
            //Получаем очки для вышешдших игроков в текущей раздаче
            $points = $pointsAmount / count($outPlayers);
            //Обновляем данные вышедших пользователей
            foreach($outPlayers as $player) {
                $player = $this->getGame()->getPlayersContainer()->getPlayer($player);
                $player->setPlay(false);
                $player->addPoints($points);
            }
        }

        //Проверка завершения партии
        $playersCount = count($this->getGame()->getPlayersContainer());
        $outPlayersCount = count($this->getGame()->getPlayersContainer()->getPlayersOutGame());
        if ($playersCount - $outPlayersCount <= 1) {
            //Инкремент сыгранных партий
            $this->getGame()->incGamesPlay();
            //Партия завершена
            return true;
        } else {
            //Партия не закончена, переключаем атакующего игрока
            $this->getGame()->getPlayersContainer()->switchActivePlayer($this->isDefend());
            return false;
        }
    }


    /**
     * Обработка достижения таймаута игроком
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

        //Флаг завершение всего матча
        $endMatch = false;
        //Если это первый розыгрыш в партии и игрок не сделал ни одного хода - проигрышь всего матча
        if ($finishMatch ||
                ($this->isFirstProccess() && count($player->getCardArray()) == Core_Game_Durak::PLAYER_CARDS_COUNT)) {
            //Установка флага завершения матча
            $endMatch = true;
            //Обнуление очков игрока
            $player->setPoints(0);
        }

        //Получение списка победителей розыгрыша,определение суммы очков за выход в партии
        $winPlayers = array();
        $points = 0;
        foreach($this->getGame()->getPlayersContainer() as $item) {
            if ($player != $item && $item->isPlay()) {
                $winPlayers[] = $item->getSid();
                //Определение общего количества очков оппонентов проигравшего
                if ($this->getGame()->getPoints() > 0) {
                    $points += $this->getGame()->getPoints();
                    $this->getGame()->setPoints($this->getGame()->getPoints() - 0.5);
                }
            }
        }

        //Получем количество очков для каждого вышедшего игрока в текущем розыгрыше
        if (count($winPlayers)) {
            $points = $points / count($winPlayers);
        }

        //Установка очков победителям за выход
        foreach($winPlayers as $player) {
            $player = $this->getGame()->getPlayersContainer()->getPlayer($player);
            $player->addPoints($points);
        }

        //Завершение партии
        $this->getGame()->finishGame($endMatch);
    }
}
