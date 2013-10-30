<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 20.02.12
 * Time: 13:50
 *
 * Описание объекта игрока
 */
class Core_Game_Players_Player
{

    /**
     * Статусы игрока в игре
     */
    const STATUS_NONE = 'close';
    const STATUS_WINNER = 'win';
    const STATUS_DRAW = 'draw';
    const STATUS_LOSER = 'lose';

    /**
     * Идентификатор сессии игрока
     *
     * @var string
     */
    protected $_sid;

    /**
     * Имя игрока
     *
     * @var string
     */
    protected $_name;

    /**
     * Идентификатор пользователя в игре
     *
     * @var mixed
     */
    protected $_id;

    /**
     * Время на ход в секундах
     *
     * @var int
     */
    protected $_runtime;

    /**
     * Время на партию в секундах
     *
     * @var int
     */
    protected $_gametime;

    /**
     * Баланс игрока
     *
     * @var int
     */
    protected $_balance;

    /**
     * Статус игрока в игре
     *
     * @var int
     */
    protected $_status = self::STATUS_NONE;

    /**
     * Сумма выигрыша игрока
     *
     * @var int
     */
    protected $_winamount = 0;

    /**
     * Флаг текущего активного игрока
     *
     * @var bool
     */
    protected $_active = false;

    /**
     * Флаг состояния игрока в партии (в игре|вне игры)
     *
     * @var bool
     */
    protected $_play = true;

    /**
     * Количество очков игрока
     *
     * @var int
     */
    protected $_points = 0;


    /**
     * Создание нового объекта игрока
     *
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Метод установки параметров модели игрока
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        if (is_array($options) && count($options) > 0) {
            foreach($options as $name => $value) {
                $this->__set($name, $value);
            }
        }
    }

    /**
     * Магический метод __get
     *
     * @param string $name
     * @throws Exception
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            throw new Exception('Unknown method ' . $method . ' called in ' . get_class($this));
        }
    }

    /**
     * Магический метод __set
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            $this->$method($value);
        }
    }

    /**
     * Установка идентификатора сессии пользователя
     *
     * @param string $sid
     * @return Core_Game_Players_Player
     */
    public function setSid($sid)
    {
        $this->_sid = $sid;
        return $this;
    }

    /**
     * Получение идентификатора сессии пользователя
     *
     * @return string
     */
    public function getSid()
    {
        return $this->_sid;
    }

    /**
     * Установка имени игрока
     *
     * @param string $name
     * @return Core_Game_Players_Player
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Получение имени игрока
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Установка идентификатора пользователя в игре
     *
     * @param mixed $id
     * @return Core_Game_Players_Player
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Получение идентификатора пользователя в игре
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Установка времени на ход игрока
     *
     * @param int $seconds
     * @return Core_Game_Players_Player
     */
    public function setRuntime($seconds)
    {
        $this->_runtime = $seconds;
        return $this;
    }

    /**
     * Получение времени на ход игрока
     *
     * @return int
     */
    public function getRuntime()
    {
        return $this->_runtime;
    }

    /**
     * Установка стартового времени на партию (в начале партии)
     *
     * @param int $seconds Кол-во секунд
     *
     * @return Core_Game_Players_Player
     */
    public function setStartGametime($seconds)
    {
        //Проверка наличия купленного товара "Песочные часы" у игрока
        //"Песочные часы" - добавление 1 минуты к времени на партию
        if (Core_Shop_Items::hasItem(Core_Shop_Items::SAND_CLOCK, $this->getSid())) {
            $seconds += 60;
        }

        //Установка стартового времени на партию
        $this->setGametime($seconds);
        return $this;
    }

    /**
     * Установка времени на партию игрока
     *
     * @param int $seconds
     * @return Core_Game_Players_Player
     */
    public function setGametime($seconds)
    {
        $this->_gametime = $seconds;
        return $this;
    }

    /**
     * Получение времени на партию игрока
     *
     * @return int
     */
    public function getGametime()
    {
        return $this->_gametime;
    }

    /**
     * Установка баланса игрока
     *
     * @param int $balance
     * @return Core_Game_Players_Player
     */
    public function setBalance($balance)
    {
        $this->_balance = $balance;
        return $this;
    }

    /**
     * Получение баланса игрока
     *
     * @return int
     */
    public function getBalance()
    {
        return $this->_balance;
    }

    /**
     * Установка статуса игрока в игре
     *
     * @param int $status
     * @return Core_Game_Players_Player
     */
    public function setStatus($status)
    {
        $this->_status = $status;
        return $this;
    }

    /**
     * Получение статуса игрока в игре
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Установка суммы выигрыша игрока
     *
     * @param int $amount
     * @return Core_Game_Players_Player
     */
    public function setWinamount($amount)
    {
        $this->_winamount = $amount;
        return $this;
    }

    /**
     * Получение суммы выигрыша игрока
     *
     * @return int
     */
    public function getWinamount()
    {
        return $this->_winamount;
    }

    /**
     * Установка активности игрока
     *
     * @param bool $active
     * @return Core_Game_Players_Player
     */
    public function setActive($active = true)
    {
        $this->_active = $active;
        return $this;
    }

    /**
     * Проверка активности игрока
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->_active;
    }

    /**
     * Установка состояния игрока в игре
     *
     * @param bool $play
     * @return Core_Game_Durak_Players_Player
     */
    public function setPlay($play = true)
    {
        $this->_play = $play;
        return $this;
    }

    /**
     * Проверка состояния игрока в игре (в игре|вне игры)
     *
     * @return bool
     */
    public function isPlay()
    {
        return $this->_play;
    }

    /**
     * Установка количества очков игрока
     *
     * @param int $points
     */
    public function setPoints($points)
    {
        $this->_points = $points;
    }

    /**
     * Получение количества очков игрока
     *
     * @return int
     */
    public function getPoints()
    {
        return $this->_points;
    }

    /**
     * Добавление очков игроку
     *
     * @param int $points
     */
    public function addPoints($points)
    {
        $this->_points += $points;
    }

    /**
     * Получение оставшегося времени на ход у игрока
     *
     * @param int $utimeLastUpdate Время последнего изменения данных игры в UNIX формате
     * @param bool $unsigned Флаг запрета отрицательного значения в результате метода
     * @return int
     */
    public function getRestRuntime($utimeLastUpdate, $unsigned = true)
    {
        //Проверка активности игрока
        if (!$this->isActive()) {
            //Таймер для неактивных пользователей не идет
            return $this->getRuntime();
        }

        //Получаем текущее время в UNIX формате
        $currentTime = time();
        //Подсчет остатка времени хода
        $restTime = $utimeLastUpdate + $this->getRuntime() - $currentTime;

        //Проверка на отрицательный результат
        if ($unsigned && $restTime < 0) {
            $restTime = 0;
        }

        return $restTime;
    }

    /**
     * Получение оставшегося времени на партию у игрока
     *
     * @param int $utimeLastUpdate Время последнего изменения данных игры в UNIX формате
     * @param bool $unsigned Флаг запрета отрицательного значения в результате метода
     * @return int
     */
    public function getRestGametime($utimeLastUpdate, $unsigned = true)
    {
        //Проверка активности игрока
        if (!$this->isActive()) {
            //Таймер для неактивных пользователей не идет
            return $this->getGametime();
        }

        //Проверка истечения времени на ход
        $restRuntime = $this->getRestRuntime($utimeLastUpdate, false);
        if ($restRuntime > 0) {
            //Возвращаем текущее время на партию игрока
            return $this->getGametime();
        }

        //Подсчет остатка времени на партию
        $restTime = $this->getGametime() + $restRuntime;

        //Проверка на отрицательный результат
        if ($unsigned && $restTime < 0) {
            $restTime = 0;
        }

        return $restTime;
    }

    /**
     * Обновление времени игрока на партию
     *
     * @param int $utimeLastUpdate Время последнего изменения данных игры в UNIX формате
     */
    public function updateGametime($utimeLastUpdate)
    {
        $restGametime = $this->getRestGametime($utimeLastUpdate);
        $this->setGametime($restGametime);
    }

    /**
     * Получение объекта в виде строки
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSid();
    }

}
