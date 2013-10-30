<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 01.03.12
 * Time: 10:11
 *
 * Модель сессии чата игры
 */
class App_Model_Session_GameChat implements App_Model_Interface
{

    /**
     * Префикс идентификатора сессии
     */
    const SID_PREFIX = 'chat:';

    /**
     * Идентификатор сессии
     *
     * @var string
     */
    protected $_sid;

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
        return array('_chat');
    }

    /**
     * Обработчик клонирования экземпляра класса
     */
    public function __clone()
    {
        $this->setChat(clone($this->getChat()));
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
     * Установка идентификатора сессии чата игры
     *
     * @param string $sid Идентификатор сессии игры
     * @return App_Model_Session_GameChat
     */
    public function setSid($sid)
    {
        $this->_sid = $this->_getChatSid($sid);
        return $this;
    }

    /**
     * Получение идентификатора сессии чата игры
     *
     * @return string
     */
    public function getSid()
    {
        return $this->_sid;
    }

    /**
     * Установка объекта данных чата
     *
     * @param Core_Game_Chat $chat
     * @return App_Model_Session_GameChat
     */
    public function setChat(Core_Game_Chat $chat)
    {
        $this->_chat = $chat;
        return $this;
    }

    /**
     * Получение объекта данных чата
     *
     * @return Core_Game_Chat
     */
    public function getChat()
    {
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
     * Сохранение данных сессии в хранилище
     *
     * @param string|null $sid [optional]
     * @return bool
     */
    public function save($sid = null)
    {
        if (null !== $sid) {
            $this->setSid($sid);
        }
        if ($this->getMapper()->save($this)) {
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
        $sid = $this->_getChatSid($sid);
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

    /**
     * Получение объекта чата
     *
     * @static
     * @param string $gameSid Идентификатор сессии игры
     * @return Core_Game_Chat
     * @throws Core_Exception
     */
    public static function chat($gameSid)
    {
        //Объект сессии чата
        $session = new self();
        //Получение данных сессии чата
        if (!$session->find($gameSid)) {
            throw new Core_Exception('Chat session was not found', 105);
        }

        //Отдаем данные чата
        return $session->getChat();
    }

    /**
     * Формирование идентификатора сессии чата
     *
     * @param string $sid Идентификатор сессии игры
     * @return string
     */
    protected function _getChatSid($sid)
    {
        if (!strstr($sid, self::SID_PREFIX)) {
            $sid = self::SID_PREFIX . $sid;
        }
        return $sid;
    }
}
