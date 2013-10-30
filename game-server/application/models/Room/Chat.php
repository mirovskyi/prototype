<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 04.06.12
 * Time: 12:41
 *
 * Модель данных чата в зале
 */
class App_Model_Room_Chat implements App_Model_Interface
{

    /**
     * Префикс пространства имен в хранилище
     */
    const STORAGE_NAMESPACE = 'rchat';

    /**
     * Пространство имен
     *
     * @var string
     */
    protected $_namespace;

    /**
     * Объект данных чата
     *
     * @var Core_Game_Chat
     */
    protected $_chat;

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
        return self::STORAGE_NAMESPACE . '::' . $this->getNamespace();
    }

    /**
     * Инициализация данных
     */
    public function init()
    {
        $this->find($this->getKey());
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
     * Установка пространства имен
     *
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
    }

    /**
     * Получение пространства имен
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Установка объекта данных чата
     *
     * @param Core_Game_Chat $chat
     */
    public function setChat(Core_Game_Chat $chat)
    {
        $this->_chat = $chat;
    }

    /**
     * Получение объекта данных чата
     *
     * @return Core_Game_Chat
     */
    public function getChat()
    {
        if (null === $this->_chat) {
            //Создание нового объекта чата
            $this->setChat(new Core_Game_Chat());
        }
        return $this->_chat;
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
     *
     * @return bool
     */
    public function find($key)
    {
        return $this->getMapper()->find($key, $this);
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
        return $this->getMapper()->unlock($this);
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
     * Блокировка и обновление данных чата
     *
     * @return void
     */
    public function lockAndUpdate()
    {
        $this->lock();
        $this->find($this->getKey());
    }

    /**
     * Сохранение и разблокировка данных чата
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
