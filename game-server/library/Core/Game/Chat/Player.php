<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 29.02.12
 * Time: 17:18
 *
 * Описание объекта игрока в чате игры
 */
class Core_Game_Chat_Player
{

    const REAL_PLAYER = 0;
    const OBSERVER_PLAYER = 1;

    /**
     * Тип пользователя (игрока/наблюдатель)
     *
     * @var int
     */
    protected $_type;

    /**
     * Идентификатор сессии пользователя
     *
     * @var string
     */
    protected $_sid;

    /**
     * Логин пользователя
     *
     * @var string
     */
    protected $_name;


    /**
     * Создание нового объекта пользователя в чате
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
     * Получение списка полей объекта для сериализации
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_type', '_name');
    }

    /**
     * Установка имени пользователя
     *
     * @param string $name
     * @return \Core_Game_Chat_Player
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Получение имени пользователя
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Установка идентификатора сессии пользователя
     *
     * @param string $sid
     * @return \Core_Game_Chat_Player
     */
    public function setSid($sid)
    {
        $this->_sid = $sid;
        return $this;
    }

    /**
     * Получения идентификатора сессии пользователя
     *
     * @return string
     */
    public function getSid()
    {
        return $this->_sid;
    }

    /**
     * Установка типа пользователя
     *
     * @param int $type
     * @return \Core_Game_Chat_Player
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * Получение типа пользователя
     *
     * @return int
     */
    public function getType()
    {
        return $this->_type;
    }

}
