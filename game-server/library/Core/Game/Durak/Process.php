<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 17:45
 *
 * Реализация розыгрыша в игре "Дурак"
 */
abstract class Core_Game_Durak_Process implements Countable
{

    /**
     * Возможное количество карт в розыгрыше
     */
    const FIRST_PROCESS_CARDS_COUNT = 5;
    const CARDS_COUNT = 6;

    /**
     * Количество времени ожидания хода в розыгрыше
     */
    const TIMEOUT = 10;

    /**
     * Карты в розыгрыше.
     * Ключ массива - подкинутая карта, значение - карта отбивающегося икрока
     *
     * @var array
     */
    protected $_cards = array();

    /**
     * Список идентификаторов пользователей, которые отказались подбрасывать карты
     *
     * @var array
     */
    protected $_refusePlayers = array();

    /**
     * Флаг первого хода в партии
     *
     * @var bool
     */
    protected $_firstProcess;

    /**
     * Объект игры
     *
     * @var Core_Game_Durak
     */
    protected $_game;

    /**
     * Флаг взятия карт бьющимся
     *
     * @var bool
     */
    protected $_lose = false;

    /**
     * Время таймаута розыгрыша (время ожидания подбрасывания карт оппонентами)
     *
     * @var int
     */
    protected $_timeout;

    /**
     * Флаг очистки данных процесса
     *
     * @var bool
     */
    protected $_cleared;


    /**
     * __construct
     *
     * @param Core_Game_Durak $game
     */
    public function __construct(Core_Game_Durak $game)
    {
        $this->setGame($game);

        //Определение первого хода в партии
        if (count($game->getPulldown())) {
            //Есть карты в отбое, не первый ход
            $this->_firstProcess = false;
            return;
        }

        //Подсчет количества карт у игроков
        foreach($game->getPlayersContainer() as $player) {
            if (count($player->getCardArray()) != Core_Game_Durak::PLAYER_CARDS_COUNT) {
                //Количество карт игрока не совпадает с количеством карт в начале игры
                $this->_firstProcess = false;
                return;
            }
        }

        //Установка флага первого хода
        $this->_firstProcess = true;
        //Получение игрока, который должен ходить первым в начале партии
        $player = $this->_getPlayerToGo();
        //Установка атакующего пользователя
        $this->getGame()->getPlayersContainer()->setAtackPlayer($player);
        //Установка активности атакующего игрока
        $this->getGame()->getPlayersContainer()->setActive($player);
        //Получаем следующего пользователя после атакующего
        $iterator = $this->getGame()->getPlayersContainer()->getIterator();
        $iterator->setCurrentElement($player);
        $defender = $iterator->nextElement();
        //Установка отбивающегося игрока
        $this->getGame()->getPlayersContainer()->setDefender($defender);
    }

    /**
     * Magic method __sleep
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_cards', '_refusePlayers', '_firstProcess', '_lose', '_timeout', '_cleared');
    }

    /**
     * Установка объекта игры
     *
     * @param Core_Game_Durak $game
     * @return Core_Game_Durak_Process
     */
    public function setGame(Core_Game_Durak $game)
    {
        $this->_game = $game;
        return $this;
    }

    /**
     * Получение объекта игры
     *
     * @return Core_Game_Durak
     */
    public function getGame()
    {
        return $this->_game;
    }

    /**
     * Получение объекта отбивающего игрока
     *
     * @return Core_Game_Durak_Players_Player|bool
     */
    public function getDefender()
    {
        return $this->getGame()->getPlayersContainer()->getDefenderPlayer();
    }

    /**
     * Полученеи объекта списка карт в розыгрыше
     *
     * @return Core_Game_Durak_Cards_Array
     */
    public function getCardArray()
    {
        $cards = new Core_Game_Durak_Cards_Array();
        foreach($this->_cards as $card1 => $card2) {
            $cards->add($card1);
            if (null !== $card2) {
                $cards->add($card2);
            }
        }

        return $cards;
    }

    /**
     * Проверка, является ли розыгрышь первым в партии
     *
     * @return bool
     */
    public function isFirstProccess()
    {
        return $this->_firstProcess;
    }

    /**
     * Установка флага взятия карт бьющим игроком
     *
     * @param bool $lose
     * @return Core_Game_Durak_Process
     */
    public function setLose($lose = true)
    {
        //Установка флага взятия карт
        $this->_lose = $lose;
        //Снятие статуса активности с отбивающегося
        $this->getDefender()->setActive(false);
        //Старт таймера
        $this->startTimer();

        return $this;
    }

    /**
     * Проверка взятия карт бьющим игроком
     *
     * @return bool
     */
    public function isLose()
    {
        return $this->_lose;
    }

    /**
     * Добавление карты в розыгрыш
     *
     * @param Core_Game_Durak_Players_Player $player Игрок, который подкидывает карту
     * @param Core_Game_Durak_Cards_Card     $card   Карта
     *
     * @throws Core_Game_Durak_Exception
     * @return void
     */
    public function addCard(Core_Game_Durak_Players_Player $player, Core_Game_Durak_Cards_Card $card)
    {
        //Проверка наличия карты на руках пользователя
        if (!$player->getCardArray()->hasCard($card)) {
            throw new Core_Game_Durak_Exception('Player has not card', 3013, Core_Exception::USER);
        }
        //Проверка возможности подкинуть карту
        if (!$this->canAddCard($player, $card, $exception)) {
            throw $exception;
        }
        //Удаляем карту из данных подкидывающего игрока
        $player->getCardArray()->delete($card);
        //Подкидываем карту
        $this->_cards[$card->__toString()] = null;

        //Очистка списка игроков, отказавшихя подкинуть карты в розыгрыш (т.к. появилась новая карта)
        if (!$this->isLose()) {
            $this->clearRefusePlayers();
        }
    }

    /**
     * Бить карту
     *
     * @param Core_Game_Durak_Cards_Card $cardInProcess Карта в розыгрыше, которую необходимо бить
     * @param Core_Game_Durak_Cards_Card $card Объект бьющей карты игрока
     * @throws Core_Game_Durak_Exception
     */
    public function beatOffCard(Core_Game_Durak_Cards_Card $cardInProcess, Core_Game_Durak_Cards_Card $card)
    {
        //Проверка наличия карты на руках у отбивающегося
        if (!$this->getDefender()->getCardArray()->hasCard($card)) {
            throw new Core_Game_Durak_Exception('Player has not card', 3013, Core_Exception::USER);
        }

        $strCardInProcess = $cardInProcess->__toString();
        $cardsInProcess = array_keys($this->_cards);
        //Проверка наличия карты в розыгрыше
        if (!in_array($strCardInProcess, $cardsInProcess)) {
            throw new Core_Game_Durak_Exception('This card is not in the process ' . implode(',', $cardsInProcess) . ' : ' . $strCardInProcess, 3005, Core_Exception::USER);
        }

        //Проверяем не отбита ли уже карта
        if ($this->_cards[$strCardInProcess] !== null) {
            throw new Core_Game_Durak_Exception('Card is allready beaten off', 3006, Core_Exception::USER);
        }

        //Прверка старшинства бьющей карты
        if ($card->isLower($cardInProcess, $this->getGame()->getPack()->getTrump())) {
            throw new Core_Game_Durak_Exception('Beating card must be upper', 3007, Core_Exception::USER);
        }

        //Удаляем карту из данных бьющегося игрока
        $this->getDefender()->getCardArray()->delete($card);
        //Установка бьющей карты
        $this->_cards[$strCardInProcess] = $card->__toString();
    }

    /**
     *  Отказ игрока подкинуть карты в розыгрыш
     *
     * @param Core_Game_Durak_Players_Player $player
     */
    public function throwRefuse(Core_Game_Durak_Players_Player $player)
    {
        if (!in_array($player->getSid(), $this->_refusePlayers)) {
            $this->_refusePlayers[] = $player->getSid();
        }
    }

    /**
     * Получение списка игроков, отказавшихся подкинуть карту.
     *
     * @return array
     */
    public function getRefusePlayers()
    {
        return $this->_refusePlayers;
    }

    /**
     * Проверка возможности игрока отказаться подкидывать карты
     *
     * @param Core_Game_Durak_Players_Player $player
     *
     * @return bool
     */
    function canRefuse(Core_Game_Durak_Players_Player $player)
    {
        //Проверяем наличие карт на руках у игрока
        if (!count($player->getCardArray())) {
            return false;
        }
        //Отбивающийся не может подкидывать карты
        if ($player == $this->getGame()->getPlayersContainer()->getDefenderPlayer()) {
            return false;
        }

        //Проверяем отбился ли игрок либо взял карты
        if (!$this->isDefend() && !$this->isLose()) {
            return false;
        }

        //Проверка наличия отказа игрока в данном розыгрыше
        if ($this->isPlayerRefuse($player)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *  Проверка отказа игрока подкинуть карты в розыгрыш
     *
     * @param Core_Game_Durak_Players_Player $player
     * @return bool
     */
    public function isPlayerRefuse(Core_Game_Durak_Players_Player $player)
    {
        return in_array($player->getSid(), $this->_refusePlayers);
    }

    /**
     * Очистка списка пользователей, отказавшихся подкинуть карты в розыгрыш
     *
     * @return void
     */
    public function clearRefusePlayers()
    {
        $this->_refusePlayers = array();
    }

    /**
     * Проверка, отбил ли игрок все карты в розыгрыше
     *
     * @return bool
     */
    public function isDefend()
    {
        if (!count($this->_cards)) {
            return false;
        }
        if ($this->isLose()) {
            return false;
        }
        return false === array_search(null, $this->_cards);
    }

    /**
     * Проверка окончания розыгрыша
     *
     * @return bool
     */
    public function isEndProcess()
    {
        //Проверяем есть ли на столе не отбитые карты и не взял ли пользователь карты
        if (!$this->isLose() && !$this->isDefend()) {
            return false;
        }

        //Проверка достижения максимального количества карт в розыгрыше
        if (count($this->_cards) == $this->_maxCardCount()) {
            return true;
        }

        //Проверяем остались ли карты у игрока, который отбивается
        if (!count($this->getDefender()->getCardArray())) {
            return true;
        }

        //Проверка возможности подкинуть карту у каждого оппонента отбивающегос
        /*foreach($this->getGame()->getPlayersContainer() as $player) {
            if ($player->isPlay() && !$this->isPlayerRefuse($player) && $this->canAddCard($player)) {
                return false;
            }
        }*/

        //Проверка отказа подкинуть карты всех активных игроков; проверка наличия карт у подкидывающих
        $isEnd = true;
        foreach($this->getGame()->getPlayersContainer() as $player) {
            //Отбивающегося пропускаем
            if ($this->getGame()->getPlayersContainer()->isDefender($player)) {
                continue;
            }
            //Проверяем возможность/желание игрока подкинуть карты
            if ($player->isPlay() && count($player->getCardArray()) && !$this->isPlayerRefuse($player)) {
                $isEnd = false;
            }
        }

        return $isEnd;
    }

    /**
     * Проверка возможности пользователя(ей) подкинуть карту
     *
     * @abstract
     *
     * @param Core_Game_Durak_Players_Player|null $player Если указан игрок, проверяется возможность игроком подкинуть карты.
     * Если игрок не указан, проверяется вообще возможность подкидывать карты в розыгрыше
     * @param Core_Game_Durak_Cards_Card|null     $card Карту которую необходимо добавить в розыгрыш
     * @param Exception|null                      $exception
     * @return bool
     */
    abstract function canAddCard(Core_Game_Durak_Players_Player $player = null, Core_Game_Durak_Cards_Card $card = null, &$exception = null);

    /**
     * Обработка завершения розыгрыша
     *
     * @abstract
     * @return bool Возвращает флаг завершения всей партии
     */
    abstract public function finish();

    /**
     * Обработка достижения таймаута игрокамом
     *
     * @abstract
     *
     * @param Core_Game_Durak_Players_Player $player
     * @param bool                           $finishMatch
     *
     * @return void
     */
    abstract public function handleTimeout(Core_Game_Durak_Players_Player $player, $finishMatch = false);

    /**
   	 * (PHP 5 >= 5.1.0)
   	 * Count elements of an object
   	 * @link http://php.net/manual/en/countable.count.php
   	 * @return int The custom count as an integer.
   	 * The return value is cast to an integer.
   	 */
    public function count()
    {
        return count($this->_cards);
    }

    /**
     * Получение карт в розыгрыше в виде строки
     *
     * @return string
     */
    public function showCards()
    {
        $cards = array();
        foreach($this->_cards as $card1 => $card2) {
            $card = $card1 . ($card2 !== null ? ':' . $card2 : '');
            $cards[] = $card;
        }
        return implode(',', $cards);
    }

    /**
     * Проверка активности таймера розыгрыша
     *
     * @return bool
     */
    public function isTimerEnable()
    {
        //Проверка завершения хода отбивающегося
        if ($this->isDefend() || $this->isLose()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получение остатка времени ожидания в розыгрыше
     *
     * @param bool $unsigned
     * @return int
     */
    public function getTimeout($unsigned = true)
    {
        $restTime = $this->_timeout - (time() - $this->getGame()->getLastUpdate());
        if ($unsigned && $restTime < 0) {
            $restTime = 0;
        }

        return $restTime;
    }

    /**
     * Старт таймера завершения розыгрыша
     */
    public function startTimer()
    {
        $this->_timeout = self::TIMEOUT;
    }

    /**
     * Получение данных розыгрыша в виде XML
     *
     * @param int|null $pos Порядковый номер пользователя для которго необходимо показывать данные розыгрыша
     * @param bool $showPlayersCards
     * @return string
     */
    public function saveXml($pos = null, $showPlayersCards = true)
    {
        //Формировние XML
        $xml = new XMLWriter();
        $xml->openMemory();

        //Получаем данные игрока с указанным местом за столом
        $iterator = $this->getGame()->getPlayersContainer()->getIterator();
        $currentPlayer = $iterator->getElement($pos);

        //Данные колоды
        $xml->startElement('pack');
        $xml->writeAttribute('trump', $this->getGame()->getPack()->getTrump());
        $xml->writeAttribute('show', $this->getGame()->getPack()->getTrumpCard());
        //Если первый розыгрыш, передаем количество карт до раздаичи (полнцю колоду)
        if ($this->isFirstProccess()) {
            $xml->text(Core_Game_Durak_Cards_Pack::CARDS_COUNT);
        } else {
            $xml->text(count($this->getGame()->getPack()));
        }
        $xml->endElement();

        //Данные отбоя
        $xml->startElement('pulldown');
        $xml->writeAttribute('count', count($this->getGame()->getPulldown()));
        $xml->endElement();

        //Данные игроков
        foreach($this->getGame()->getPlaces() as $pos => $player) {
            $xml->startElement('user');
            $xml->writeAttribute('pos', $pos);
            //Проверка необходимости отображать карты пользователя
            if ($showPlayersCards) {
                if (!$currentPlayer || $currentPlayer == $player) {
                    //Показываем открытые карты
                    $xml->text($player->getCardArray());
                } else {
                    //Показываем закрытые карты
                    $xml->text($player->getCardArray()->showHiddenCards());
                }
            }
            $xml->endElement();
        }

        //Данные карт в розыгрыше
        $xml->writeElement('process', $this);

        //Возвращаем данные розыгрыша в виде XML
        return $xml->flush(false);
    }

    /**
     * Получение данных в виде строки
     *
     * @return string
     */
    public function __toString()
    {
        $result = array();
        foreach($this->_cards as $card1 => $card2) {
            $result[] = $card1 . ($card2 ? ':' . $card2 : '');
        }
        return implode(',', $result);
    }

    /**
     * Получение максимального количесто карт в розыгрыше
     *
     * @return int
     */
    protected function _maxCardCount()
    {
        if (!count($this->getGame()->getPulldown())) {
            //Первый отбой
            return self::FIRST_PROCESS_CARDS_COUNT;
        } else {
            return self::CARDS_COUNT;
        }
    }

    /**
     * Раздача карт пользователям по окончанию розыгрыша
     *
     * @return void
     */
    protected function _dealCards()
    {
        //Проверка наличия карт в колоде
        if (!count($this->getGame()->getPack())) {
            return;
        }
        //Установка курсора итератора игроков на текущего отбивающего
        $playerIterator = $this->getGame()->getPlayersContainer()->getIterator();
        $playerIterator->setCurrentElement($this->getDefender());
        //Перебор всех пользователей до отбивающего
        while($playerIterator->nextElement() != $this->getDefender()) {
            //Раздача карт пользователю
            $this->getGame()->dealCards($playerIterator->current());
        }
        //Раздача карт отбивавшему
        $this->getGame()->dealCards($this->getDefender());
    }

    /**
     * Получение игрока, который должен ходить первым в начале партии
     *
     * @return Core_Game_Players_Player
     */
    private function _getPlayerToGo()
    {
        //Проверка первой партии в матче
        if ($this->getGame()->getPlayersContainer()->getAvaragePoints() == 0) {
            //Получаем пользователя с наименьшим страшинством корызной карты
            $firstPlayer = $this->_getPlayerWithMinTrumpCard();
            if ($firstPlayer) {
                //Получаем козырную карту с наименьшим старшинством
                $card = $firstPlayer->getCardArray()->getMinValue($this->getGame()->getPack()->getTrump());
                //Добавляем анимацию показа карты первого хода
                $position = $this->getGame()->getPlayersContainer()->getIterator()->getElementIndex($firstPlayer);
                $this->getGame()->getAnimation()->addAction(
                    $this->getGame()->getCommand(),
                    Core_Game_Durak_Animation::GOCARD,
                    $position, $card
                );
            } else {
                //У пользователей на руках нет козырей, берем первого пользователя в списке
                $firstPlayer = $this->getGame()->getPlayersContainer()->getIterator()->getCurrentElement();
            }

            //Возвращаем игрока для первого хода в партии
            return $firstPlayer;
        }

        //Если не первая партия, получаем следующего игрока после последнего отбивающегося в прошлой партии
        $lastDefender = $this->getGame()->getPlayersContainer()->getDefenderPlayer();
        $iterator = $this->getGame()->getPlayersContainer()->getIterator();
        $iterator->setCurrentElement($lastDefender);
        return $iterator->nextElement();
    }

    /**
     * Получение объекта игрока, у которого наименьший козырь
     *
     * @return Core_Game_Durak_Players_Player|null
     */
    private function _getPlayerWithMinTrumpCard()
    {
        //Получаем пользователя с наименьшим страшинством корызной карты
        $min = Core_Game_Durak_Cards_Card::ACE + 1;
        $firstPlayer = null;
        foreach($this->getGame()->getPlayersContainer() as $player) {
            //Получаем козырь наименьшего старшинства у пользователя
            $card = $player->getCardArray()->getMinValue($this->getGame()->getPack()->getTrump());
            //Проверка минимальной козырной карты
            if ($card && $card->getValue() < $min) {
                $min = $card->getValue();
                $firstPlayer = $player;
            }
        }
        //Возвращаем игрока с наименьшим козырем
        return $firstPlayer;
    }

}
