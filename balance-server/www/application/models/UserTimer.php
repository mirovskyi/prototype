<?php

/**
 * ORM модель таблицы user_timers
 */
class App_Model_UserTimer
{

    /**
     * Поле таблицы, id
     *
     * @var integer
     */
    protected $_id = null;

    /**
     * Поле таблицы, id_user
     *
     * @var integer
     */
    protected $_idUser = null;

    /**
     * Поле таблицы, hour_bonus
     *
     * @var integer
     */
    protected $_hourBonus = null;

    /**
     * Поле таблицы, daily_bonus
     *
     * @var integer
     */
    protected $_dailyBonus = null;

    /**
     * Поле таблицы, friend_present
     *
     * @var integer
     */
    protected $_friendPresent = null;

    /**
     * Объект модели доступа к данным таблицы
     *
     * @var App_Model_Mapper_UserTimer
     */
    protected $_mapper = null;

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
     *
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
     * Метод установки значения поля id
     *
     * @param integer $id
     * @return App_Model_UserTimer
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Метод получения значения поля id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Метод установки значения поля id_user
     *
     * @param integer $idUser
     * @return App_Model_UserTimer
     */
    public function setIdUser($idUser)
    {
        $this->_idUser = $idUser;
        return $this;
    }

    /**
     * Метод получения значения поля id_user
     *
     * @return integer
     */
    public function getIdUser()
    {
        return $this->_idUser;
    }

    /**
     * Метод установки значения поля hour_bonus
     *
     * @param integer $hourBonus
     * @return App_Model_UserTimer
     */
    public function setHourBonus($hourBonus)
    {
        $this->_hourBonus = $hourBonus;
        return $this;
    }

    /**
     * Метод получения значения поля hour_bonus
     *
     * @return integer
     */
    public function getHourBonus()
    {
        return $this->_hourBonus;
    }

    /**
     * Метод установки значения поля daily_bonus
     *
     * @param integer $dailyBonus
     * @return App_Model_UserTimer
     */
    public function setDailyBonus($dailyBonus)
    {
        $this->_dailyBonus = $dailyBonus;
        return $this;
    }

    /**
     * Метод получения значения поля daily_bonus
     *
     * @return integer
     */
    public function getDailyBonus()
    {
        return $this->_dailyBonus;
    }

    /**
     * Метод установки значения поля friend_present
     *
     * @param integer $friendPresent
     * @return App_Model_UserTimer
     */
    public function setFriendPresent($friendPresent)
    {
        $this->_friendPresent = $friendPresent;
        return $this;
    }

    /**
     * Метод получения значения поля friend_present
     *
     * @return integer
     */
    public function getFriendPresent()
    {
        return $this->_friendPresent;
    }

    /**
     * Метод установки объекта модели доступа к
     * данным таблицы
     *
     * @param App_Model_Mapper_UserTimer $mapper
     * @return App_Model_UserTimer
     */
    public function setMapper(App_Model_Mapper_UserTimer $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Метод получения объекта модели доступа к
     * данным таблицы
     *
     * @return App_Model_Mapper_UserTimer
     */
    public function getMapper()
    {
        if (!$this->_mapper instanceof App_Model_Mapper_UserTimer) {
            $this->setMapper(new App_Model_Mapper_UserTimer());
        }
        return $this->_mapper;
    }

    /**
     * Получение объекта выборки
     *
     * @return Zend_Db_Select
     */
    public function select()
    {
        return $this->getMapper()->select();
    }

    /**
     * Метод поиска данных записи по первичному
     * ключу (id)
     *
     * @param integer|string $id
     * @return App_Model_UserTimer
     */
    public function find($id)
    {
        $this->getMapper()->find($this, $id);
        return $this;
    }

    /**
     * Метод сохранения объекта в БД
     *
     * @return integer|boolean
     */
    public function save()
    {
        return $this->getMapper()->save($this);
    }

    /**
     * Метод поиска и получения одной записи из БД
     *
     * @param string|array|Zend_Db_Table_Select $where Условие запроса
     * @param string|array $order Условие сортировки
     * @return App_Model_UserTimer
     */
    public function fetchRow($where = null, $order = null)
    {
        $this->getMapper()->fetchRow($this, $where, $order);
        return $this;
    }

    /**
     * Метод поиска и получения записей из БД
     *
     * @param string|array|Zend_Db_Table_Select $where Условие запроса
     * @param string|array $order Условие сортировки
     * @param integer $count Количество записей в
     * результате
     * @param integer $offset Номер записи, с которой ведется
     * поиск
     * @return App_Model_UserTimer[]
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAll($where, $order, $count, $offset);
    }

    /**
     * Удаление записи из БД
     *
     * @param string|array $where Условие выборки записей для
     * удаления
     * @return boolean
     */
    public function delete($where)
    {
        return $this->getMapper()->delete($where);
    }


}

