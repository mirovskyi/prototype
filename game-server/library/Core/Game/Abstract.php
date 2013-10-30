<?php

/**
 * Абстракция игры
 *
 * @author aleksey
 */
abstract class Core_Game_Abstract implements SplSubject
{
    
    /**
     * Статусы игры
     */
    const STATUS_WAIT = 'WAIT';
    const STATUS_PLAY = 'PLAY';
    const STATUS_FINISH = 'FINISH';
    const STATUS_ENDGAME = 'ENDGAME';

    /**
     * Уникальный идентификатор игры
     *
     * @var string
     */
    protected $_id;

    /**
     * Порядковый номер команды (изменения данных игры)
     *
     * @var int
     */
    protected $_command;

    /**
     * Текущий статус игры
     *
     * @var string
     */
    protected $_status;

    /**
     * Время последнего обновления данных игры
     *
     * @var int
     */
    protected $_lastUpdate;

    /**
     * Время на ход
     *
     * @var int
     */
    protected $_runTimeout = 10;

    /**
     * Время на партию
     *
     * @var int
     */
    protected $_gameTimeout = 60;

    /**
     * Максимальное количество игроков
     *
     * @var int
     */
    protected $_playersCount = 2;

    /**
     * Объект данных игроков
     *
     * @var Core_Game_Players_Container
     */
    protected $_players;

    /**
     * Сумма ставки партии
     *
     * @var int
     */
    protected $_bet = 1;

    /**
     * Стартовая ставка в игре (указанная при создании игры)
     *
     * @var int
     */
    protected $_startBet;

    /**
     * Список объектов событий в игре
     *
     * @var array Массив событий реализующих интерфейс Core_Game_Event_Interface
     */
    protected $_events = array();

    /**
     * Список наблюдателей
     *
     * @var SplObjectStorage
     */
    protected $_observers;


    /**
     * Создание новой игры
     */
    public function __construct()
    {
        //Инициализация списка наблюдателей
        $this->_observers = new SplObjectStorage();
        //Установка начального статуса игры
        $this->setStatus(Core_Game_Abstract::STATUS_WAIT);
        //Инициализация объекта контейнера игроков
        $this->setPlayersContainer(new Core_Game_Players_Container($this->_playersCount));
        //Установка начального порядкового номера обновления игры
        $this->setCommand(0);
        //Установка времени последнего изменения игры (инициализации)
        $this->setLastUpdate(time());
    }

    /**
     * Восстановление данных экземпляра класса после unserialize
     *
     * @return void
     */
    public function __wakeup()
    {
        //Установка ссылки на объект текущей игры в событиях
        foreach($this->_events as $event) {
            $event->setGameObject($this);
        }
    }

    /**
     * Установка уникального идентификатора игры (идентификатор сессии игры)
     *
     * @param string $id
     * @return Core_Game_Abstract
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Получение уникального идентификатора игры (идентификатор сессии игры)
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }
    
    /**
     * Метод установки порядкового номера команды (команды обновления данных игры)
     *
     * @param integer $command
     * @return Core_Game_Abstract 
     */
    public function setCommand($command)
    {
        $this->_command = (int) $command;

        return $this;
    }
    
    /**
     * Метод получения порядкового номера команды
     *
     * @return integer 
     */
    public function getCommand()
    {
        return $this->_command;
    }
    
    /**
     * Метод инкремента порядкового номера команды
     *
     * @return integer 
     */
    public function incCommand()
    {
        $command = $this->getCommand() + 1;
        $this->setCommand($command);
        return $command;
    }

    /**
     * Установка максимального количества игроков
     *
     * @param int $count
     * @return Core_Game_Abstract
     */
    public function setMaxPlayersCount($count)
    {
        $this->_playersCount = $count;
        //Обновление контейнера игроков
        $this->getPlayersContainer()->getIterator()->setElementsCount($count);
        return $this;
    }

    /**
     * Получение максимального количества игроков в игре
     *
     * @return int
     */
    public function getMaxPlayersCount()
    {
        return $this->_playersCount;
    }

    /**
     * Установка текущего статуса игры
     *
     * @param string $status
     * @return Core_Game_Abstract
     */
    public function setStatus($status)
    {
        $this->_status = $status;
        return $this;
    }

    /**
     * Получение текущего статуса игры
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Получение статуса игры для отображния клиенту игрока
     * В случае когда игра была закончена, один игрок вышел в лобби, а на его место сел другой - изменяется статус игры на WAIT и
     * увеличивается порядковый номер обновления игры. При этом первому игроку поверх окна выбора действия (Играть еше, Другой оппонент,
     * Выйти в зал) появляется окно "Ожидание оппонента" т.к. изменился статус на WAIT.
     * Метод проверяет если статус игры WAIT, а игрок "вне игры", отображать ему статус FINISH
     *
     * @param $playerSid Идентификатор сессии пользователя, для которого необходимо отображать статус
     *
     * @return string
     */
    public function getViewStatus($playerSid)
    {
        //Проверка текущего статуса
        if ($this->getStatus() != self::STATUS_WAIT) {
            //Возвращаем текущий статус
            return $this->getStatus();
        }
        //Получаем данные игрока
        $player = $this->getPlayersContainer()->getPlayer($playerSid);
        if (!$player) {
            //Для наблюдателей возвращаем текущий статус
            return $this->getStatus();
        }
        //Если игрок вне игры отдаем статус FINISH
        if (!$player->isPlay()) {
            return self::STATUS_FINISH;
        } else {
            return $this->getStatus();
        }
    }

    /**
     * Установка времени последнего обновления данных игры
     *
     * @param int|null $time
     * @return Core_Game_Abstract
     */
    public function setLastUpdate($time = null)
    {
        if (null === $time) {
            $time = time();
        }

        $this->_lastUpdate = $time;
        return $this;
    }

    /**
     * Получаение времени последнего обновления
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->_lastUpdate;
    }

    /**
     * Установка времени на ход
     *
     * @param int $seconds
     * @return Core_Game_Abstract
     */
    public function setRunTimeout($seconds)
    {
        $this->_runTimeout = $seconds;
        return $this;
    }

    /**
     * Получение времени на ход
     *
     * @return integer
     */
    public function getRunTimeout()
    {
        return $this->_runTimeout;
    }

    /**
     * Установка времени на партию
     *
     * @param $seconds
     * @return Core_Game_Abstract
     */
    public function setGameTimeout($seconds)
    {
        $this->_gameTimeout = $seconds;
        return $this;
    }

    /**
     * Получение времени на партию
     *
     * @return int
     */
    public function getGameTimeout()
    {
        return $this->_gameTimeout;
    }

    /**
     * Получение остатка времени хода текущего активного игрока
     *
     * @return int|bool
     */
    public function getActivePlayerRuntime()
    {
        //Получаем объект активного игрока
        $activePlayer = $this->getPlayersContainer()->getActivePlayer();
        if (!$activePlayer) {
            return false;
        }

        //Проверка статуса игры
        if ($this->getStatus() == self::STATUS_PLAY) {
            //Расчитываем оставшееся время на ход у активного игрока
            return $activePlayer->getRestRuntime($this->getLastUpdate());
        } else {
            //Возвращаем текущий остаток времени на ход активного игрока
            return $activePlayer->getRuntime();
        }
    }

    /**
     * Получение остатка времени партии текущего активного игрока
     *
     * @param bool $unsigned Флаг запрета отрицательного значения в результате метода
     *
     * @return int
     */
    public function getActivePlayerGametime($unsigned = true)
    {
        //Получаем объект активного игрока
        $activePlayer = $activePlayer = $this->getPlayersContainer()->getActivePlayer();
        if (!$activePlayer) {
            return false;
        }

        //Проверка статуса игры
        if ($this->getStatus() == self::STATUS_PLAY) {
            //Расчитываем оставшееся время партии активного игрока
            return $activePlayer->getRestGametime($this->getLastUpdate(), $unsigned);
        } else {
            //Возвращаем текущий остаток времени на партию активного игрока
            return $activePlayer->getGametime();
        }
    }

    /**
     * Получение остатка времени игрока на ход
     *
     * @param Core_Game_Players_Player $player
     * @param bool $unsigned Флаг запрета отрицательного значения в результате метода
     * @return int
     */
    public function getPlayerRuntime(Core_Game_Players_Player $player, $unsigned = true)
    {
        if ($this->getStatus() == self::STATUS_PLAY) {
            return $player->getRestRuntime($this->getLastUpdate(), $unsigned);
        } else {
            return 0;
        }
    }

    /**
     * Получение остатка времени игрока на партию
     *
     * @param Core_Game_Players_Player $player
     * @param bool $unsigned Флаг запрета отрицательного значения в результате метода
     * @return int
     */
    public function getPlayerGametime(Core_Game_Players_Player $player, $unsigned = true)
    {
        if ($this->getStatus() == self::STATUS_PLAY) {
            return $player->getRestGametime($this->getLastUpdate(), $unsigned);
        } else {
            return $player->getGametime();
        }
    }

    /**
     * Установка объекта контейнера игроков
     *
     * @param Core_Game_Players_Container $players
     * @return Core_Game_Abstract
     */
    public function setPlayersContainer(Core_Game_Players_Container $players)
    {
        $this->_players = $players;
        return $this;
    }

    /**
     * Получение объекта контейнера игроков
     *
     * @return Core_Game_Players_Container|Core_Game_Players_Player[]
     */
    public function getPlayersContainer()
    {
        return $this->_players;
    }

    /**
     * Получение массива игровых мест за столом.
     * Ключ элемента массива - порядковый номер позиции за столом
     * Значение элемента массива - объект игрока либо NULL
     *
     * @return Core_Game_Players_Player[]
     */
    public function getPlaces()
    {
        //Количество мест в игре
        $placesCount = $this->getMaxPlayersCount();
        //Формирование списка мест за игровым столом
        $places = array();
        for($pos = 1; $pos <= $placesCount; $pos++) {
            $player = $this->getPlayersContainer()->getIterator()->getElement($pos);
            if (false !== $player) {
                $places[$pos] = $player;
            } else {
                $places[$pos] = null;
            }
        }

        //Возвращаем список игровых мест
        return $places;
    }

    /**
     * Добавление игрока
     *
     * @param string $sid Идентификатор сессии пользователя
     * @param string $name Имя игрока
     * @param mixed $id Идентификатор пользователя в игре
     * @param int|null $runtime Время игрока на ход
     * @param int|null $gametime Время игрока на партию
     * @param int|null $index Порядковый номер пользователя в игре
     * @return Core_Game_Players_Player
     */
    public function addPlayer($sid, $name, $id, $runtime = null, $gametime = null, $index = null)
    {
        //Установка времени хода и игры по умолчанию
        if (null === $runtime) {
            $runtime = $this->getRunTimeout();
        }
        if (null === $gametime) {
            $gametime = $this->getGameTimeout();
        }

        //Создание объекта игрока
        $player = new Core_Game_Players_Player(array(
            'sid' => $sid,
            'id' => $id,
            'name' => $name,
            'runtime' => $runtime,
            'startGametime' => $gametime
        ));

        //Добавление игрока
        $this->getPlayersContainer()->addPlayer($player, $index);

        //Возвращаем объект добавленного игрока
        return $player;
    }

    /**
     * Установка суммы ставки в партии
     *
     * @param int $amount
     * @return Core_Game_Abstract
     */
    public function setBet($amount)
    {
        $this->_bet =  $amount;
        return $this;
    }

    /**
     * Получение суммы ставки в патии
     *
     * @return int
     */
    public function getBet()
    {
        return $this->_bet;
    }

    /**
     * Установка начальной ставки в игре (при создании игры)
     *
     * @param int $bet
     *
     * @return Core_Game_Abstract
     */
    public function setStartBet($bet)
    {
        $this->_startBet = $bet;
        return $this;
    }

    /**
     * Получение начальной ставки в игре
     *
     * @return int
     */
    public function getStartBet()
    {
        return $this->_startBet;
    }

    /**
     * Установка победителя в игре
     *
     * @param Core_Game_Players_Player|string $player
     * @param int|null $winamount
     */
    public function setWinner($player, $winamount = null)
    {
        //Установка статуса победителя указанному игроку, другим проигрыш
        foreach($this->getPlayersContainer() as $element) {
            if ($element == $player) {
                //Установка победы
                $element->setStatus(Core_Game_Players_Player::STATUS_WINNER);
                //Установка суммы выигрыша
                if (null !== $winamount) {
                    $element->setWinamount($winamount);
                }
            } else {
                //Установка поражения
                $element->setStatus(Core_Game_Players_Player::STATUS_LOSER)
                        ->setWinamount(0);
            }
        }
    }

    /**
     * Установка статуса ничьи для всех игроков
     *
     * @param int|null $winamount Сумма выигрыша каждого игрока
     */
    public function setDraw($winamount = null)
    {
        //Установка статуса ничьи всем игрокам без статуса
        foreach($this->getPlayersContainer() as $player) {
            if ($player->getStatus() == Core_Game_Players_Player::STATUS_NONE) {
                $player->setStatus(Core_Game_Players_Player::STATUS_DRAW);
                //Установка суммы выигрыша игрока
                if (null !== $winamount) {
                    $player->setWinamount($winamount);
                }
            }
        }
    }

    /**
     * Установка проигравшего в игре
     *
     * @param Core_Game_Players_Player|string $player
     */
    public function setLoser($player)
    {
        $player = $this->getPlayersContainer()->getPlayer($player);
        if ($player) {
            $player->setStatus(Core_Game_Players_Player::STATUS_LOSER)
                   ->setWinamount(0);
        }
    }

    /**
     * Очистка данных о статусах игроков
     *
     * @return void
     */
    public function clearPlayersStatus()
    {
        foreach($this->getPlayersContainer() as $player) {
            $player->setStatus(Core_Game_Players_Player::STATUS_NONE)
                   ->setWinamount(0);
        }
    }

    /**
     * Установка событый в игре
     *
     * @param array $events
     */
    public function setEvents(array $events)
    {
        foreach($events as $event) {
            $this->addEvent($event);
        }
    }

    /**
     * Получение списка текущих событий в игре
     *
     * @return Core_game_Event[]
     */
    public function getEvents()
    {
        //Возвращаем список событий
        return $this->_events;
    }

    /**
     * Добавление события в игру
     *
     * @param Core_Game_Event $event
     * @throws Core_Exception
     * @return Core_Game_Abstract
     */
    public function addEvent(Core_Game_Event $event)
    {
        //Проверка наличия неотработанного "одиночного" события такого же типа
        if ($this->hasEventType($event->getType())) {
            //Получаем первое списке событие такого же типа
            foreach($this->getEvents() as $e) {
                if ($e->getType() == $event->getType()) {
                    break;
                }
            }
            //Проверка флага "одиночки"
            if ($e->isSingle()) {
                throw new Core_Exception('Previous event has not processed yet', 1500, Core_Exception::USER);
            }
        }
        //Установка ссылки на объект игры
        $event->setGameObject($this);
        //Добавление события
        $this->_events[$event->getName()] = $event;
        return $this;
    }

    /**
     * Получение события в игре
     *
     * @param string $eventName
     * @return Core_Game_Event
     */
    public function getEvent($eventName)
    {
        if (isset($this->_events[$eventName])) {
            //Возвращаем объект события
            return $this->_events[$eventName];
        } else {
            return false;
        }
    }

    /**
     * Проверка наличия события в игре
     *
     * @param string $eventName
     * @return bool
     */
    public function hasEvent($eventName)
    {
        return isset($this->_events[$eventName]);
    }

    /**
     * Проверка наличия типа событий в игре
     *
     * @param string $eventType
     * @return bool
     */
    public function hasEventType($eventType)
    {
        foreach($this->getEvents() as $event) {
            if ($event->getType() == $eventType) {
                return true;
            }
        }

        return false;
    }

    /**
     * Удаление события
     *
     * @param string $eventName
     */
    public function deleteEvent($eventName)
    {
        if (isset($this->_events[$eventName])) {
            unset($this->_events[$eventName]);
        }
    }

    /**
     * Обработка события
     *
     * @param string $eventName
     */
    public function handleEvent($eventName)
    {
        if ($this->hasEvent($eventName)) {
            //Получаем объект события
            $event = $this->getEvent($eventName);
            //Обработка события
            $event->handle();
        }
    }
    
    /**
     * Обновление состояния игры
     */
    public function updateGameState()
    {
        $this->setLastUpdate(time())
             ->incCommand();
    }

    /**
     * Добавление объекта наблюдателя
     *
     * @param SplObserver $observer
     */
    public function attach(SplObserver $observer)
    {
        $this->_observers->attach($observer);
    }

    /**
     * Удаление объекта наблюдателя
     *
     * @param SplObserver $observer
     */
    public function detach(SplObserver $observer)
    {
        $this->_observers->detach($observer);
    }

    /**
     * Удаление всех наблюдателей
     */
    public function detachAll()
    {
        $this->_observers->removeAll($this->_observers);
    }

    /**
     * Оповещение всех зарегистрированных наблюдателей
     */
    public function notify()
    {
        foreach($this->_observers as $observer) {
            $observer->update($this);
        }
    }

    /**
     * Получение объекта списка наблюдателей
     *
     * @return SplObjectStorage
     */
    public function getObservers()
    {
        return $this->_observers;
    }
    
    /**
     * Получение системного имени игры
     *
     * @abstract
     * 
     * @return string
     */
    abstract public function getName();

    /**
     * Проверка возможности начать игру за игровым столом (изменить статус игры на PLAY)
     *
     * @abstract
     *
     * @return bool
     */
    abstract public function canPlay();

    /**
     * Проверка матчевой игры
     *
     * @abstract
     * @return bool
     */
    abstract public function isMatch();

    /**
     * Генерация начального состояния игрового стола
     *
     * @abstract
     *
     * @return void
     */
    abstract public function generate();

    /**
     * Добавлние пустого действия в историю анимированного обновления данных игры.
     * В осносном пустые анимации нужны для событий, которые изменяют порядковый номер обновления игры (command)
     * Напимер при увеличении ставки инкрементится прядковый номер, но кроме суммы ставки данные игрового стола не меняются
     * Для того чтобы события не повлияли на порядок отображения анимации, необходимо в историю анимации добавлять пустое действие, которое визуально ничего не меняет за игровым столом
     *
     * @abstract
     * @return void
     */
    abstract public function addEmptyAnimation();

    /**
     * Получение текущего состояния игры для записи в историю (XML)
     *
     * @abstract
     *
     * @return string
     */
    abstract public function saveHistory();
    
}