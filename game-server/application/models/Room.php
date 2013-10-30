<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 03.04.12
 * Time: 16:39
 *
 * Модель данных игрового зала
 */
class App_Model_Room implements App_Model_Interface
{

    /**
     * Пространство имен игрового зала в хранилище
     */
    const STORAGE_NAMESPACE = 'room';

    /**
     * Просиранство имен зала (имя игры)
     *
     * @var string
     */
    protected $_namespace;

    /**
     * Список идентификаторов сессий игроков в зале и идентификаторов сессий игровых столов за которыми сидят игрока
     * ключ - сесси игрока, значение - сессия игрового стола или FALSE
     *
     * @var array
     */
    protected $_users = array();

    /**
     * Список идентификаторов сессий игровых столов
     *
     * @var array
     */
    protected $_games = array();

    /**
     * Объект доступа к хранилищу данных
     *
     * @var App_Model_Mapper_Interface
     */
    protected $_mapper;

    /**
     * __construct
     *
     * @param $namespace
     * @param bool $init
     */
    public function __construct($namespace, $init = true)
    {
        $this->setNamespace($namespace);
        if ($init) {
            $this->init();
        }
    }

    /**
     * Получение ключа записи в хранилище
     *
     * @return string
     */
    public function getKey()
    {
        return self::STORAGE_NAMESPACE . ':' . $this->getNamespace();
    }

    /**
     * Инициализация данных
     */
    public function init()
    {
        $this->find($this->getNamespace());
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
        return array('_namespace' ,'_users','_games');
    }

    /**
     * Установка пространства имен (имя игры)
     *
     * @param string $namespace
     * @return App_Model_Room
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
        return $this;
    }

    /**
     * Получение пространства имен (имя игры)
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Установка списка идентификаторов сессий пользователей в игровом зале
     *
     * @param array $users
     * @return App_Model_Room
     */
    public function setUsers(array $users)
    {
        $this->_users = $users;
        return $this;
    }

    /**
     * Получение данных пользователей в игровом зале
     *
     * @return array
     */
    public function getUsers()
    {
        return $this->_users;
    }

    /**
     * Получение массива идентификаторов сесий пользователей в игровом зале
     *
     * @return array
     */
    public function getUsersSid()
    {
        return array_keys($this->_users);
    }

    /**
     * Добавление идентификатора пользователя в игровой зал
     *
     * @param string $userSid
     * @param string|bool $gameSid Идентификатор сессии игрового стола, за которым сидит игрок, либо FALSE
     * @return App_Model_Room
     */
    public function addUser($userSid, $gameSid = false)
    {
        $this->_users[$userSid] = $gameSid;
        return $this;
    }

    /**
     * Проверка наличия идентификатора сессии пользователя в игровом зале
     *
     * @param string $userSid
     * @return bool
     */
    public function hasUser($userSid)
    {
        return isset($this->_users[$userSid]);
    }

    /**
     * Установка идентификатора сессии игрового стола, за которым сидит игрок
     *
     * @param string $userSid Идентификатор сессии игрока
     * @param string $gameSid Идентификатор сессии игры
     * @return App_Model_Room
     */
    public function setUserInGame($userSid, $gameSid)
    {
        if (isset($this->_users[$userSid])) {
            $this->_users[$userSid] = $gameSid;
        }
        return $this;
    }

    /**
     * Проверка наличия пользователя за игровым столом
     *
     * @param string $userSid
     * @return bool
     */
    public function isUserInGame($userSid)
    {
        if (!isset($this->_users[$userSid])) {
            return false;
        }
        return $this->_users[$userSid];
    }

    /**
     * Получение идентификатора сессии игрового зала, за которым сидит игрок
     *
     * @param string $userSid Идентификатор сессии игрока
     *
     * @return string|bool
     */
    public function getUserGame($userSid)
    {
        return $this->_users[$userSid];
    }

    /**
     * Удаление идентификатора сессии пользователя из игрового зала
     *
     * @param string $userSid
     */
    public function delUser($userSid)
    {
        if (isset($this->_users[$userSid])) {
            unset($this->_users[$userSid]);
        }
    }

    /**
     * Установка списка идентификаторов сессий игровых столов
     *
     * @param array $games
     * @return App_Model_Room
     */
    public function setGames(array $games)
    {
        $this->_games = $games;
        return $this;
    }

    /**
     * Получение списка идентификаторов сессий игровых столов
     *
     * @return array
     */
    public function getGames()
    {
        return $this->_games;
    }

    /**
     * Добавление идентификатора сессии игры в игровой зал
     *
     * @param string $gameSid
     * @return App_Model_Room
     */
    public function addGame($gameSid)
    {
        if (!in_array($gameSid, $this->_games)) {
            $this->_games[] = $gameSid;
        }
        return $this;
    }

    /**
     * Проверка наличия идентификатора сессии игрового стола в зале
     *
     * @param string $gameSid
     * @return bool
     */
    public function hasGame($gameSid)
    {
        return in_array($gameSid, $this->_games);
    }

    /**
     * Удаление идентификатора сессии игрового стола из зала
     *
     * @param string $gameSid
     */
    public function delGame($gameSid)
    {
        $index = array_search($gameSid, $this->_games);
        if (false !== $index) {
            unset($this->_games[$index]);
        }
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
     * Поиск данных модели
     *
     * @param string $key
     * @return bool
     */
    public function find($key)
    {
        $this->setNamespace($key);
        return $this->getMapper()->find($this->getKey(), $this);
    }

    /**
     * Сохранение данных модели
     *
     * @return bool
     */
    public function save()
    {
        return $this->getMapper()->save($this);
    }

    /**
     * Удаление данных модели
     *
     * @return bool
     */
    public function delete()
    {
        return $this->getMapper()->delete($this);
    }

    /**
     * Блокировка данных модели
     *
     */
    public function lock()
    {
        $this->getMapper()->lock($this);
    }

    /**
     * Разблокировка данных модели
     *
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
     * Блокировка и обновление данных игрового зала
     *
     * @return void
     */
    public function lockAndUpdate()
    {
        $this->lock();
        $this->find($this->getNamespace());
    }

    /**
     * Сохранение и разблокировка данных игрового зала
     *
     * @return void
     */
    public function saveAndUnlock()
    {
        $this->save();
        $this->unlock();
    }

    /**
     * Получение модели в виде строки
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getKey();
    }
}
