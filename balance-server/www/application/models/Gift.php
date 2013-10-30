<?php

/**
 * ORM модель таблицы gifts
 */
class App_Model_Gift
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
     * Поле таблицы, id_user_from
     *
     * @var integer
     */
    protected $_idUserFrom = null;

    /**
     * Поле таблицы, id_user_to
     *
     * @var integer
     */
    protected $_idUserTo = null;

    /**
     * Поле таблицы, name
     *
     * @var string
     */
    protected $_name = null;

    /**
     * Поле таблицы, create_date
     *
     * @var string
     */
    protected $_createDate = null;

    /**
     * Поле таблицы, is_received
     *
     * @var boolean
     */
    protected $_isReceived = null;

    /**
     * Поле таблицы, received_date
     *
     * @var string
     */
    protected $_receivedDate = null;

    /**
     * Объект модели доступа к данным таблицы
     *
     * @var App_Model_Mapper_Gift
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
     * @return App_Model_Gift
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
     * @return App_Model_Gift
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
     * Метод установки значения поля id_user_from
     *
     * @param integer $idUserFrom
     * @return App_Model_Gift
     */
    public function setIdUserFrom($idUserFrom)
    {
        $this->_idUserFrom = $idUserFrom;
        return $this;
    }

    /**
     * Метод получения значения поля id_user_from
     *
     * @return integer
     */
    public function getIdUserFrom()
    {
        return $this->_idUserFrom;
    }

    /**
     * Метод установки значения поля id_user_to
     *
     * @param integer $idUserTo
     * @return App_Model_Gift
     */
    public function setIdUserTo($idUserTo)
    {
        $this->_idUserTo = $idUserTo;
        return $this;
    }

    /**
     * Метод получения значения поля id_user_to
     *
     * @return integer
     */
    public function getIdUserTo()
    {
        return $this->_idUserTo;
    }

    /**
     * Метод установки значения поля name
     *
     * @param string $name
     * @return App_Model_Gift
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Метод получения значения поля name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Метод установки значения поля create_date
     *
     * @param string $createDate
     * @return App_Model_Gift
     */
    public function setCreateDate($createDate)
    {
        $this->_createDate = $createDate;
        return $this;
    }

    /**
     * Метод получения значения поля create_date
     *
     * @return string
     */
    public function getCreateDate()
    {
        return $this->_createDate;
    }

    /**
     * Метод установки значения поля is_received
     *
     * @param boolean $isReceived
     * @return App_Model_Gift
     */
    public function setIsReceived($isReceived)
    {
        $this->_isReceived = $isReceived;
        return $this;
    }

    /**
     * Метод получения значения поля is_received
     *
     * @return boolean
     */
    public function getIsReceived()
    {
        return $this->_isReceived;
    }

    /**
     * Метод установки значения поля received_date
     *
     * @param string $receivedDate
     * @return App_Model_Gift
     */
    public function setReceivedDate($receivedDate)
    {
        $this->_receivedDate = $receivedDate;
        return $this;
    }

    /**
     * Метод получения значения поля received_date
     *
     * @return string
     */
    public function getReceivedDate()
    {
        return $this->_receivedDate;
    }

    /**
     * Метод установки объекта модели доступа к
     * данным таблицы
     *
     * @param App_Model_Mapper_Gift $mapper
     * @return App_Model_Gift
     */
    public function setMapper(App_Model_Mapper_Gift $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Метод получения объекта модели доступа к
     * данным таблицы
     *
     * @return App_Model_Mapper_Gift
     */
    public function getMapper()
    {
        if (!$this->_mapper instanceof App_Model_Mapper_Gift) {
            $this->setMapper(new App_Model_Mapper_Gift());
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
     * @return App_Model_Gift
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
     * @return App_Model_Gift
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
     * @return App_Model_Gift[]
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

