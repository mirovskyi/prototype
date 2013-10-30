<?php

class App_Model_Session_Game implements App_Model_Interface
{

    /**
     * Доступные режимы игровых столов
     */
    const PUBLIC_MODE = 0;
    const PRIVATE_MODE = 1;

    /**
     * Режимы наблюдателей
     */
    const SPECTATOR_DENY = 0;
    const SPECTATOR_ALLOW = 1;
    
    /**
     * Идентификатор сессии
     *
     * @var string 
     */
    protected $_sid;

    /**
     * Идентификатор сессии пользователя, создавшего игровой стол
     *
     * @var string
     */
    protected $_creatorSid;
    
    /**
     * Системное имя игры
     *
     * @var string 
     */
    protected $_name;

    /**
     * Флаг создания игрового стола пользователем с VIP статусом
     *
     * @var bool
     */
    protected $_vip = false;

    /**
     * Режим игрового стола (публичный|приватный)
     *
     * @var int
     */
    protected $_mode;

    /**
     * Режим наблюдения (разрешено|запрещено)
     *
     * @var int
     */
    protected $_spectator;

    /**
     * Минимальный баланс оппонентов
     *
     * @var int
     */
    protected $_minBalance;

    /**
     * Минимальный опыт оппонентов
     *
     * @var int
     */
    protected $_minExperience;

    /**
     * Флаг права первого хода
     *
     * @var int (0 - случайно, 1 - создатель, 2 - оппонент)
     */
    protected $_firstMove;

    /**
     * Список приглашенных пользователей
     *
     * @var array
     */
    protected $_invites = array();

    /**
     * Список наблюдателей
     *
     * @var array
     */
    protected $_spectators = array();
    
    /**
     * Данные игры
     *
     * @var Core_Game_Abstract|null
     */
    protected $_data;
    
    /**
     * Объект доступа к хранилищу данных
     *
     * @var App_Model_Mapper_Interface
     */
    protected $_mapper;
    
    
    /**
     * __construct
     *
     * @param array $options
     */
    public function __construct($options = null)
    {
        $this->setOptions($options);
    }

    /**
     * Метод установки параметров модели
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
     * Магический метод __sleep
     *
     * @return array 
     */
    public function __sleep() 
    {
        return array(
            '_name',
            '_vip',
            '_creatorSid',
            '_mode',
            '_spectator',
            '_spectators',
            '_minBalance',
            '_minExperience',
            '_firstMove',
            '_invites',
            '_data'
        );
    }

    /**
     * Обработчик клонирования экземпляра класса
     */
    public function __clone()
    {
        $this->setData(clone($this->getData()));
    }

    /**
     * Получение ключа записи в хранилище
     *
     * @return string
     */
    public function getKey()
    {
        return $this->getSid();
    }
    
    /**
     * Установка идентификатора сессии
     *
     * @param string $sid
     * @return App_Model_Session_Game 
     */
    public function setSid($sid)
    {
        $this->_sid = $sid;
        return $this;
    }
    
    /**
     * Получение идентификатора сесии
     *
     * @return string 
     */
    public function getSid()
    {
        return $this->_sid;
    }

    /**
     * Установка идентификатор сессии
     *
     * @param string $sid
     * @return App_Model_Session_Game
     */
    public function setCreatorSid($sid)
    {
        $this->_creatorSid = $sid;
        return $this;
    }

    /**
     * Получение идентификатора сессии пользователя, создавшего игровой стол
     *
     * @return string
     */
    public function getCreatorSid()
    {
        return $this->_creatorSid;
    }
    
    /**
     * Установка системного имени игры
     *
     * @param string $name
     * @return App_Model_Session_Game 
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }
    
    /**
     * Получение системного имени игры
     *
     * @return string 
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Установка VIP статуса игрового стола
     *
     * @param bool $vip
     *
     * @return App_Model_Session_Game
     */
    public function setVip($vip = true)
    {
        $this->_vip = $vip;
        return $this;
    }

    /**
     * Получение VIP статуса игрового стола
     *
     * @return bool
     */
    public function getVip()
    {
        return $this->_vip;
    }

    /**
     * Проверка VIP статуса игрового стола
     *
     * @return bool
     */
    public function isVip()
    {
        return $this->_vip;
    }

    /**
     * Установка режима игрового стола (публичный|приватный)
     *
     * @param int $mode
     * @return App_Model_Session_Game
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;
        return $this;
    }

    /**
     * Получение режима игрового стола (публичный|приватный)
     *
     * @return int
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Установка режима наблюдения за игровым столом (разрешено|запрещено)
     *
     * @param int|bool $spctr
     * @return App_Model_Session_Game
     */
    public function setSpectator($spctr)
    {
        $this->_spectator = intval($spctr);
        return $this;
    }

    /**
     * Получение режима наблюдения за игровым столом (разрешено|запрещено)
     *
     * @return int
     */
    public function getSpectator()
    {
        return $this->_spectator;
    }

    /**
     * Установка минимального баланса оппонентов
     *
     * @param int $minBalance
     * @return App_Model_Session_Game
     */
    public function setMinBalance($minBalance)
    {
        $this->_minBalance = $minBalance;
        return $this;
    }

    /**
     * Получение минимального баланса оппонентов
     *
     * @return int
     */
    public function getMinBalance()
    {
        return $this->_minBalance;
    }

    /**
     * Установка минимального опыта оппонентов
     *
     * @param $minExperience
     * @return App_Model_Session_Game
     */
    public function setMinExperience($minExperience)
    {
        $this->_minExperience = $minExperience;
        return $this;
    }

    /**
     * Получение минимального опыта оппонента
     *
     * @return int
     */
    public function getMinExperience()
    {
        return $this->_minExperience;
    }

    /**
     * Установка флага права первого хода (0 - случайно, 1 - создатель, 2 - оппонент)
     *
     * @param int $fm
     *
     * @return App_Model_Session_Game
     */
    public function setFirstMove($fm)
    {
        $this->_firstMove = $fm;
        return $this;
    }

    /**
     * Получение флага права первого хода
     *
     * @return int (0 - случайно, 1 - создатель, 2 - оппонент)
     */
    public function getFirstMove()
    {
        return $this->_firstMove;
    }

    /**
     * Установка списка идентификаторов сессий приглашенных пользователей
     *
     * @param array $users
     * @return App_Model_Session_Game
     */
    public function setInvites(array $users)
    {
        $this->_invites = $users;
        return $this;
    }

    /**
     * Получение списка идентификаторов сессий приглашенных пользователей
     *
     * @return array
     */
    public function getInvites()
    {
        return $this->_invites;
    }

    /**
     * Добавление идентификатора сессии пользователя в список приглашенных за игровой стол
     *
     * @param string $userSid
     * @return App_Model_Session_Game
     */
    public function addInvite($userSid)
    {
        if (!in_array($userSid, $this->_invites)) {
            $this->_invites[] = $userSid;
        }

        return $this;
    }

    /**
     * Проверка наличия идентификатора сесси пользоваля в списке приглашеннх
     *
     * @param string $userSid
     * @return bool
     */
    public function hasInvite($userSid)
    {
        return in_array($userSid, $this->_invites);
    }

    /**
     * Удаление идентификатора сессии пользователя из списка приглашенных
     *
     * @param string $userSid
     */
    public function delInvite($userSid)
    {
        $index = array_search($userSid, $this->_invites);
        if (false !== $index) {
            unset($this->_invites[$index]);
        }
    }

    /**
     * Установка списка наблюдателей
     *
     * @param array $spectators
     *
     * @return App_Model_Session_Game
     */
    public function setSpectators(array $spectators)
    {
        $this->_spectators = $spectators;
        return $this;
    }

    /**
     * Получение списка наблюдателей
     *
     * @return array
     */
    public function getSpectators()
    {
        return $this->_spectators;
    }

    /**
     * Добавление наблюдателя за игровой стол
     *
     * @param string $sid Идентификатор сессии пользователя
     * @param bool   $canSitdown Возможность наблюдателя сесть за игровой стол
     *
     * @return App_Model_Session_Game
     */
    public function addSpectator($sid, $canSitdown = false)
    {
        $this->_spectators[$sid] = $canSitdown;
        return $this;
    }

    /**
     * Удаление наблюдателя из игрового стола
     *
     * @param string $sid Идентификатор сессии наблюдателя
     *
     * @return void
     */
    public function delSpectator($sid)
    {
        if (isset($this->_spectators[$sid])) {
            unset($this->_spectators[$sid]);
        }
    }

    /**
     * Проверка наличия пользователя в списке наблюдателей
     *
     * @param string $sid Идентификатор сессии пользователя
     *
     * @return bool
     */
    public function isSpectator($sid)
    {
        return isset($this->_spectators[$sid]);
    }

    /**
     * Проверка возможности пользователя сесть за игровой стол
     *
     * @param string $sid Идентификатор сессии пользователя
     *
     * @return bool
     */
    public function canUserSitdown($sid)
    {
        //Проверка наличия пользователя в списке наблюдателей
        if (!isset($this->_spectators[$sid])) {
            //Пользователи которые уже сидят за столом не могут садится на свободные места
            return false;
        }
        //Проверка наличия свободных мест за игровым столом
        $playersCount = count($this->getData()->getPlayersContainer());
        if ($this->getData()->getMaxPlayersCount() <= $playersCount) {
            //Нет свободных мест
            return false;
        }
        //Возвращаем возможности наблюдателя сесть за игровой стол
        return $this->_spectators[$sid];
    }
    
    /**
     * Установка данных игры
     *
     * @param Core_Game_Abstract $data
     * @return App_Model_Session_Game 
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }
    
    /**
     * Получение данных игры
     *
     * @return Core_Game_Abstract 
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * Установка времени последнего пинга игры
     *
     * @param string $timestamp
     * @return App_Model_Session_Game 
     */
    public function setLastPingDate($timestamp = null)
    {
        if (null === $timestamp) {
            $timestamp = date('U');
        }
        $key = 'ping:' . $this->getSid();
        $this->getMapper()->getImplementation()->set($key, $timestamp);
        return $this;
    }
    
    /**
     * Получение времени последнего пинга игры
     *
     * @return string
     */
    public function getLastPingData()
    {
        $key = 'ping:' . $this->getSid();
        return $this->getMapper()->getImplementation()->get($key);
    }

    /**
     * Очистка данных о последнем пинге сессии
     *
     * @return int
     */
    public function clearLastPingData()
    {
        $key = 'ping:' . $this->getSid();
        return $this->getMapper()->getImplementation()->delete($key);
    }
    
    /**
     * Установка объекта доступа к хранилищу данных
     *
     * @param App_Model_Mapper_Interface $mapper
     * @return App_Model_Session_Game 
     */
    public function setMapper(App_Model_Mapper_Interface $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }
    
    /**
     * Получение объекта доступа к хранилищу данных
     *
     * @return App_Model_Mapper_Interface
     */
    public function getMapper()
    {
        if (!$this->_mapper instanceof App_Model_Mapper_Interface) {
            $this->setMapper(new App_Model_Mapper_Storage());
        }
        return $this->_mapper;
    }

    /**
     * Сохранение данных сессии в хранилище
     *
     * @param string|null $sid [optional]
     *
     * @throws Exception
     * @return bool
     */
    public function save($sid = null)
    {
        if (null !== $sid) {
            $this->setSid($sid);
        }

        //Установка уникального идентификатора игры
        $this->getData()->setId($this->getSid());

        //Оповещение слушателей об обновлении состояния игры
        try {
            $this->getData()->notify();
        } catch (Exception $e) {
            //Если данные сессии залочены, освобождаем ключ блокировки
            if ($this->isLock(posix_getpid())) {
                $this->unlock();
            }
            //Выбрасываем исключение
            throw $e;
        }

        //Сохранение данных игры
        if ($this->getMapper()->save($this)) {
            //Создание/обновление записи со временем последнего пинга от клиента
            $this->setLastPingDate();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Поиск данных в хранилище
     *
     * @param string $sid
     * @return bool
     */
    public function find($sid)
    {
        if ($this->getMapper()->find($sid, $this)) {
            $this->setSid($sid);
            return true;
        }
        return false;
    }

    /**
     * Блокировка данных сессии
     */
    public function lock()
    {
        $this->getMapper()->lock($this);
    }

    /**
     * Разблокировка данных сессии
     */
    public function unlock()
    {
        $this->getMapper()->unlock($this);
    }

    /**
     * Проверка блокировки данных модели
     * В случае передачи параметра pid, проверяется блокировка данных модели указанным процессом
     *
     * @param string|null $pid
     * @return bool
     */
    public function isLock($pid = null)
    {
        return $this->getMapper()->isLock($this, $pid);
    }

    /**
     * Блокировка и обновление данных сессии
     *
     * @return void
     */
    public function lockAndUpdate()
    {
        $this->lock();
        $this->find($this->getSid());
    }

    /**
     * Сохранение и разблокировка данных сессии
     *
     * @return void
     */
    public function saveAndUnlock()
    {
        $this->save();
        $this->unlock();
    }
    
    /**
     * Удаление данных сессии из хранилища
     *
     * @return bool 
     */
    public function delete()
    {
        return $this->getMapper()->delete($this);
    }
    
    /**
     * Метод получение объекта сессии в виде строки
     *
     * @return string 
     */
    public function __toString()
    {
        return $this->getSid();
    }
}
