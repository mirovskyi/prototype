<?php

class App_Model_Session_User implements App_Model_Interface, Core_Shop_Items_Interface
{

    /**
     * Время превращения пользователя в "зомби"
     */
    const ZOMBIE_TIME = 30;

    /**
     * Ключ записи сессии в хранилище
     *
     * @var string
     */
    protected $_key;
    
    /**
     * Идентификатор сессии (Идентификатор пользователя в игровом сервисе)
     *
     * @var string 
     */
    protected $_sid;
    
    /**
     * Системное имя социальной сети
     *
     * @var string
     */
    protected $_network;

    /**
     * IP адрес пользователя
     *
     * @var string
     */
    protected $_ip;

    /**
     * Список купленных товаров пользователя
     *
     * @var array
     */
    protected $_items = array();
    
    /**
     * Данные сессии
     *
     * @var string 
     */
    protected $_data;

    /**
     * Идентификатор сессии игры, в которой участвует игрок
     *
     * @var string
     */
    protected $_gameSid;

    /**
     * Флаг удаления сессии игрока (в случае если игрок инициализировал другую сессию, текущие помечаются как удаленные и со временем удаляются)
     *
     * @var bool
     */
    protected $_deleted = false;

    /**
     * Объект данных игрока
     *
     * @var Core_Social_User
     */
    protected $_socialUser;

    /**
     * Список уведомлений пользователя
     *
     * @var array
     */
    protected $_notifications = array();
    
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
        return array('_sid', '_network', '_ip', '_items', '_data', '_notifications', '_deleted');
    }

    /**
     * Установка ключа записи
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
    }

    /**
     * Получение ключа записи в хранилище
     *
     * @return string
     */
    public function getKey()
    {
        if (null == $this->_key && $this->getSid()) {
            $this->setKey($this->_getKeyBySid());
        }
        return $this->_key;
    }
    
    /**
     * Установка идентификатора сессии
     *
     * @param string $sid
     * @return App_Model_Session_User 
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
     * Установка системного имени социальной сети
     *
     * @param string $network
     * @return App_Model_Session_User 
     */
    public function setNetwork($network)
    {
        $this->_network = $network;
        return $this;
    }
    
    /**
     * Получение системного имени социальной сети
     *
     * @return string 
     */
    public function getNetwork()
    {
        return $this->_network;
    }

    /**
     * Установка IP адреса пользователя
     *
     * @param string $ip
     *
     * @return App_Model_Session_User
     */
    public function setIp($ip)
    {
        $this->_ip = $ip;
        return $this;
    }

    /**
     * Получение IP адреса пользователя
     *
     * @return string
     */
    public function getIp()
    {
        return $this->_ip;
    }
    
    /**
     * Установка данных сессии
     *
     * @param array $data
     * @return App_Model_Session_User 
     */
    public function setData($data)
    {
        $this->_data = $data;
        $this->_socialUser = null;
        return $this;
    }
    
    /**
     * Получение данных сессии
     *
     * @return array 
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Установка идентификатора сессии игры, в которой принимаеи участие пользователь
     *
     * @param string $gameSid
     * @return App_Model_Session_User
     */
    public function setGameSid($gameSid)
    {
        $this->_gameSid = $gameSid;
        return $this;
    }

    /**
     * Получение идентификатора сессии игры, в которой принимает усастие игрок
     *
     * @return string
     */
    public function getGameSid()
    {
        return $this->_gameSid;
    }

    /**
     * Удаление связи пользователя с игрой
     *
     * @return void
     */
    public function clearGameSid()
    {
        $this->_gameSid = null;
    }

    /**
     * Установка флага удаления сессии игрока
     *
     * @param bool $del
     *
     * @return App_Model_Session_User
     */
    public function setDeleted($del = true)
    {
        $this->_deleted = $del;
        return $this;
    }

    /**
     * Получение флага удаления сессии
     *
     * @return bool
     */
    public function getDeleted()
    {
        return $this->_deleted;
    }

    /**
     * Проверка флага удаления сессии игрока
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->_deleted;
    }

    /**
     * Установка объекта данных пользователя соц. сети
     *
     * @param Core_Social_User $socialUser
     * @return App_Model_Session_User
     */
    public function setSocialUser(Core_Social_User $socialUser)
    {
        $this->_socialUser = $socialUser;
        return $this;
    }

    /**
     * Получение объекта данных пользователя соц. сети
     *
     * @return Core_Social_User
     */
    public function getSocialUser()
    {
        if (null === $this->_socialUser) {
            $socialUser = new Core_Social_User($this->getNetwork(), $this->getData());
            $this->setSocialUser($socialUser);
        }
        return $this->_socialUser;
    }

    /**
     * Установка списка ключей уведомлений
     *
     * @param array $notifications
     * @return App_Model_Session_User
     */
    public function setNotifications(array $notifications)
    {
        $this->_notifications = $notifications;
        return $this;
    }

    /**
     * Получение списка ключей уведомлений
     *
     * @return array
     */
    public function getNotifications()
    {
        return $this->_notifications;
    }

    /**
     * Добавление ключа уведомления
     *
     * @param string $notificationKey
     * @return App_Model_Session_User
     */
    public function addNotification($notificationKey)
    {
        if (!in_array($notificationKey, $this->_notifications)) {
            $this->_notifications[] = $notificationKey;
        }

        return $this;
    }

    /**
     * Проверка наличия оповещения с указанным ключем
     *
     * @param string $notificationKey
     * @return bool
     */
    public function hasNotification($notificationKey)
    {
        return in_array($notificationKey, $this->_notifications);
    }

    /**
     * Удаление ключа уведомления
     *
     * @param string $notificationKey
     */
    public function delNotification($notificationKey)
    {
        $index = array_search($notificationKey, $this->_notifications);
        if (false !== $index) {
            unset($this->_notifications[$index]);
        }
    }
    
    /**
     * Установка времени последнего пинга от пользователя
     *
     * @param string $timestamp
     * @return App_Model_Session_User 
     */
    public function setLastPingDate($timestamp = null)
    {
        if (null === $timestamp) {
            $timestamp = date('U');
        }
        $key = 'ping:' . $this->getSid();
        Core_Storage::factory()->set($key, $timestamp);
        return $this;
    }
    
    /**
     * Получение времени последнего пинга от пользователя
     *
     * @return string
     */
    public function getLastPingData()
    {
        $key = 'ping:' . $this->getSid();
        return Core_Storage::factory()->get($key);
    }

    /**
     * Очистка данных о времени последнего пинга пользователя
     *
     * @return int
     */
    public function clearLastPingData()
    {
        $key = 'ping:' . $this->getSid();
        return Core_Storage::factory()->delete($key);
    }

    /**
     * Проверка неактивности игрока (игрок-зомби)
     *
     * @return bool
     */
    public function isZombie()
    {
        $sleepTime = time() - $this->getLastPingData();
        return $sleepTime > self::ZOMBIE_TIME;
    }
    
    /**
     * Установка объекта доступа к хранилищу данных
     *
     * @param App_Model_Mapper_Interface $mapper
     * @return App_Model_Session_User
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
     * @param string|null $key [optional]
     * @return bool 
     */
    public function save($key = null)
    {
        //Установка ключа записи
        if (null !== $key) {
            $this->setKey($key);
        }
        //Сохраняем ключ записи в хранилище под идентификатором пользователя в приложении
        $this->_saveKey();

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
     * @param string $key
     * @return bool
     */
    public function find($key)
    {
        //Проверка типа переданного ключа
        if (strlen($key) != 32) {
            //Передан идентификатор пользователя, получаем ключ записи по идентификатору пользователя
            $key = $this->_getKeyBySid($key);
        }
        //Проверка наличия ключа
        if (!$key) {
            return false;
        }
        //Поиск данных сессии в хранилище
        if ($this->getMapper()->find($key, $this)) {
            $this->setKey($key);
            return true;
        }
        return false;
    }

    /**
     * Поиск данных в хранилище только по указанному ключу (без поиска ключа по идентификатору сессии пользователя)
     *
     * @param string $key
     * @return bool
     */
    public function findOnlyByKey($key)
    {
        if ($this->getMapper()->find($key, $this)) {
            $this->setKey($key);
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
        if ($this->getMapper()->delete($this)) {
            //Удаление ключа
            $this->_deleteKey();
        }
        return false;
    }

    /**
     * Получение данных пользователей соц. сети из контейнера игроков
     *
     * @static
     * @param Core_Game_Players_Container $container
     * @return array
     */
    public static function getUsersDataFromPlayersContainer(Core_Game_Players_Container $container)
    {
        $users = array();
        foreach($container as $player) {
            $user = new self();
            if ($user->find($player->getSid())) {
                $users[$user->getSid()] = $user;
            }
        }

        return $users;
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

    /**
     * Установка списка товаров
     *
     * @param array $items
     */
    public function setItems(array $items)
    {
        $this->_items = $items;
    }

    /**
     * Добавление товара
     *
     * @param string $itemName
     */
    public function addItem($itemName)
    {
        //Проверка формата переданного товара
        if (strstr($itemName, ':')) {
            //Формат [наименование]:[время окончания действия]
            //Разделяем имя товара и его срок действия
            $item = explode(':', $itemName);
            $name = $item[0];
            $deadline = $item[0];
        } else {
            //Передано только имя товара, установка безконечного срока действия
            $name = $itemName;
            $deadline = 0;
        }

        //Установка данных товара в список
        $this->_items[$name] = $deadline;
    }

    /**
     * Получение списка товаров
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Получение списка активных товаров пользователя
     *
     * @return array
     */
    public function getItemsString()
    {
        //Список названий товаров
        $items = array_keys($this->_items);
        //Проверка истечения срока действия товаров и возможности использования товара в текущей игре
        foreach($items as $key => $item) {
            if (!$this->hasItem($item) || !Core_Shop_Items::isUseInGame($item)) {
                unset($items[$key]);
            }
        }
        //Возвращаем строку списока названий товаров
        return implode(',', $items);
    }

    /**
     * Проверка наличия товара
     *
     * @param string $itemName
     * @return bool
     */
    public function hasItem($itemName)
    {
        //Проверка наличия товара в списке
        if (!isset($this->_items[$itemName])) {
            return false;
        }

        //Проверка истечения срока действия товара
        $deadline = $this->_items[$itemName];
        if ($deadline > 0 && $deadline < time()) {
            //Товар просрочен
            return false;
        } else {
            return true;
        }
    }

    /**
     * Сохранение ключа записи по ключу идентификатора игрока в приложении
     */
    protected function _saveKey()
    {
        if ($this->getKey() && $this->getSid()) {
            Core_Storage::factory()->set('uid:' . $this->getSid(), $this->getKey());
        }
    }

    /**
     * Получение ключа записи по идентификатору игрока в приложении
     *
     * @param int $sid Идентификатор пользователя в приложении
     * @return bool|string
     */
    protected function _getKeyBySid($sid = null)
    {
        if (null === $sid) {
            $sid = $this->getSid();
        }

        if ($sid) {
            return Core_Storage::factory()->get('uid:' . $sid);
        } else {
            return false;
        }
    }

    /**
     * Удаление ключа записи по ключу идентификатора игрока в приложении
     */
    protected function _deleteKey()
    {
        if ($this->getSid()) {
            Core_Storage::factory()->delete('uid:' . $this->getSid());
        }
    }
}
