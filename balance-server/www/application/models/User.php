<?php

/**
 * ORM модель таблицы users
 */
class App_Model_User
{

    /**
     * Поле таблицы, id
     *
     * @var integer
     */
    protected $_id = null;

    /**
     * Поле таблицы, id_service
     *
     * @var integer
     */
    protected $_idService = null;

    /**
     * Поле таблицы, id_service_user
     *
     * @var string
     */
    protected $_idServiceUser = null;

    /**
     * Поле таблицы, balance
     *
     * @var integer
     */
    protected $_balance = null;

    /**
     * Поле таблицы, flags
     *
     * @var string
     */
    protected $_flags = null;

    /**
     * Объект модели доступа к данным таблицы
     *
     * @var App_Model_Mapper_User
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
     * @return App_Model_User
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
     * Метод установки значения поля id_service
     *
     * @param integer $idService
     * @return App_Model_User
     */
    public function setIdService($idService)
    {
        $this->_idService = $idService;
        return $this;
    }

    /**
     * Метод получения значения поля id_service
     *
     * @return integer
     */
    public function getIdService()
    {
        return $this->_idService;
    }

    /**
     * Метод установки значения поля id_service_user
     *
     * @param string $idServiceUser
     * @return App_Model_User
     */
    public function setIdServiceUser($idServiceUser)
    {
        $this->_idServiceUser = $idServiceUser;
        return $this;
    }

    /**
     * Метод получения значения поля id_service_user
     *
     * @return string
     */
    public function getIdServiceUser()
    {
        return $this->_idServiceUser;
    }

    /**
     * Метод установки значения поля balance
     *
     * @param integer $balance
     * @return App_Model_User
     */
    public function setBalance($balance)
    {
        $this->_balance = $balance;
        return $this;
    }

    /**
     * Метод получения значения поля balance
     *
     * @return integer
     */
    public function getBalance()
    {
        return $this->_balance;
    }

    /**
     * Метод установки значения поля flags
     *
     * @param string $flags
     * @return App_Model_User
     */
    public function setFlags($flags)
    {
        $this->_flags = $flags;
        return $this;
    }

    /**
     * Метод получения значения поля flags
     *
     * @return string
     */
    public function getFlags()
    {
        return $this->_flags;
    }

    /**
     * Метод установки объекта модели доступа к
     * данным таблицы
     *
     * @param App_Model_Mapper_User $mapper
     * @return App_Model_User
     */
    public function setMapper(App_Model_Mapper_User $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Метод получения объекта модели доступа к
     * данным таблицы
     *
     * @return App_Model_Mapper_User
     */
    public function getMapper()
    {
        if (!$this->_mapper instanceof App_Model_Mapper_User) {
            $this->setMapper(new App_Model_Mapper_User());
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
     * @return App_Model_User
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
     * @return App_Model_User
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
     * @return App_Model_User[]
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

