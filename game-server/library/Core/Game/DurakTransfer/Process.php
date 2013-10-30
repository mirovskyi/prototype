<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 02.07.12
 * Time: 14:10
 *
 * Реализация розыгрыша в игре "Дурак переводной"
 */
abstract class Core_Game_DurakTransfer_Process extends Core_Game_Durak_Process
{

    /**
     * Установка ссылки на объект игры
     *
     * @param Core_Game_Durak $game
     *
     * @return Core_Game_DurakTransfer_Process
     */
    public function setGame(Core_Game_Durak $game)
    {
        $this->_game = $game;
        return $this;
    }

    /**
     * Получение ссылки на объект игры
     *
     * @return Core_Game_DurakTransfer
     */
    public function getGame()
    {
        return $this->_game;
    }

    /**
     * Возможность отбивающегося перевести карты на оппонента
     *
     * @param Core_Game_Durak_Cards_Card[] $transferCards Карта для перевода
     * @param bool                         $show          Флаг показа карты при переводе
     *
     * @return bool
     */
    public function canTransfer($transferCards, $show = false)
    {
        //Проверка возможности перевода в текущем розыгрыше
        if (!$this->_canTransfer()) {
            return false;
        }

        //Проверка возможности показать козырную карту при переводе
        if ($show) {
            //Показвается только одна козырная карта
            if (count($transferCards) > 1) {
                return false;
            }
            //Козырная ли карта показана
            if ($transferCards[0]->getSuit() != $this->getGame()->getPack()->getTrump()) {
                return false;
            }
            //Показать козырную карту для перевода можно только один раз за партию
            if ($this->getGame()->isPlayerShowTrumpCard($this->getDefender())) {
                return false;
            }
        }

        //Получаем список карт в розыгрыше
        $cards = array_keys($this->_cards);
        //Проверка соответствия карт для перевода с картами в розыгрыше
        $card = Core_Game_Durak_Cards_Card::create($cards[0]);
        foreach($transferCards as $transferCard) {
            if (!$transferCard->equalValue($card)) {
                return false;
            }
        }

        //Есть возможность перевода карт
        return true;
    }

    /**
     * Перевод карт на оппонента
     *
     * @param Core_Game_Durak_Cards_Card[] $transferCards Карта для перевода
     * @param bool                         $show          Флаг показа карты при переводе
     *
     * @throws Core_Game_DurakTransfer_Exception
     * @return bool
     */
    public function transfer($transferCards, $show = false)
    {
        //Получаем объект игрока, который переводит карты (!!!После переключения активного игрока, метод getDefender() будет возвращать ссылку уже на другого игрока)
        $player = $this->getDefender();
        //Переход хода
        $this->getGame()->getPlayersContainer()->switchActivePlayer();
        //У игрока, которому переводят, должно быть больше карт чем сейчас на столе (для возможности перевести хоть одну карту)
        if (count($this->getDefender()->getCardArray()) <= count($this->_cards)) {
            //Невозможно перевести карты, у следующего отбивающегося недостаточно карт для того чтобы их отбить
            throw new Core_Game_DurakTransfer_Exception('Player can\'t transfer cards', 3014, Core_Exception::USER);
        }
        //Проверка необходимости показать карту
        if ($show) {
            //Покаываем карту для перевода
            $this->getGame()->getAnimation()->addAction(
                $this->getGame()->getCommand(),
                Core_Game_Durak_Animation::GOCARD,
                $this->getGame()->getPlayersContainer()->getPlayerPosition($player),
                $transferCards[0]->__toString()
            );
            //Добавляем игрока в список показавших козырь при переводе (эта возможность дается один раз за партию)
            $this->getGame()->addShowTrumpCardPlayer($player);
        } else {
            //Подкидываем карты в розыгрыш
            $this->getGame()->throwCards($player, $transferCards);
        }
    }

    /**
     * Получение информации о возможности перевода в виде XML
     *
     * @param int $pos Позиция игрока для которого необходимо отображать данные
     *
     * @return string
     */
    public function getTransferInfoXml($pos)
    {
        //Получение игрока по позиции
        $player = $this->getGame()->getPlayersContainer()->getIterator()->getElement($pos);
        if (!$player) {
            return '';
        }
        //Проверка на отбивающегося игрока
        if ($player != $this->getDefender()) {
            return '';
        }
        //Проверка возможности перевода
        if (!$this->_canTransfer()) {
            return '';
        }

        //Получение списка возможных карт для перевода
        $transferCards = implode(',', $this->_getCardsForTransfer());
        //Получение карты для показа при переводе
        $showCard = '';
        if ($transferCards) {
            $showCard = $this->_getShowCardForTransfer();
        }

        //Формирование данных в виде xml
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startElement('transfer');
        $xml->writeAttribute('cards', $transferCards);
        $xml->writeAttribute('show', $showCard);
        $xml->endElement();
        //Отдаем данные возможности перевода в виде xml
        return $xml->flush(false);
    }

    /**
     * Получение списка карт отбивающегося игрока для перевода
     *
     * @return array
     */
    protected function _getCardsForTransfer()
    {
        //Получение одной карты в розыгрыше
        $cardInProcess = array_pop(array_keys($this->_cards));
        //Массив карт пользователя возможных для перевода
        $transferCards = array();
        foreach($this->getDefender()->getCardArray()->getCards() as $card) {
            if ($card->equalValue($cardInProcess)) {
                $transferCards[] = $card;
            }
        }
        return $transferCards;
    }

    /**
     * Получение карты отбивающегося для показа при переводе
     *
     * @return bool|string
     */
    protected function _getShowCardForTransfer()
    {
        //Проверяем переводил ли игрок карты показом козырной карты (можно только один раз за партию)
        if ($this->getGame()->isPlayerShowTrumpCard($this->getDefender())) {
            return false;
        }
        //Получение одной карты в розыгрыше
        $cardInProcess = array_pop(array_keys($this->_cards));
        //Масть козыря
        $trump = $this->getGame()->getPack()->getTrump();
        //Поиск козырной карты для перевода
        foreach($this->getDefender()->getCardArray()->getCards() as $card) {
            if ($card->equalSuit($trump) && $card->equalValue($cardInProcess)) {
                //Возвращаем данные карты для показа при переводе (в виде строки)
                return $card->__toString();
            }
        }

        return false;
    }

    /**
     * Проверка возможности перевода карт
     *
     * @return bool
     */
    private function _canTransfer()
    {
        //Проверка наличия карт а розыгрыше
        if (!count($this->_cards)) {
            return false;
        }
        //Проверка достижения максимального количества карт на игровом столе
        if ($this->_maxCardCount() == count($this->_cards)) {
            return false;
        }
        //Поверяем нет ли отбитых карт
        foreach($this->_cards as $beatoffCard) {
            if (null !== $beatoffCard) {
                return false;
            }
        }

        //Получаем список подкинутых карт в розыгрыше
        $cards = array_keys($this->_cards);
        //Проверка наличия у отбивающегося игрока карты нужного старшинства
        foreach($this->getDefender()->getCardArray()->getCards() as $card) {
            if ($card->equalValue($cards[0])) {
                //Есть карта для перевода
                return true;
            }
        }
        //Нет карт для перевода
        return false;
    }

}
