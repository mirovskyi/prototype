<?php

/**
 * ORM модель таблицы notifications
 */
class App_Model_Notification
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
     * Поле таблицы, name
     *
     * @var string
     */
    protected $_name = null;

    /**
     * Поле таблицы, params
     *
     * @var string
     */
    protected $_params = null;

    /**
     * Объект модели доступа к данным таблицы
     *
     * @var App_Model_Mapper_Notification
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
     * @return App_Model_Notification
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
     * @return App_Model_Notification
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
     * Метод установки значения поля name
     *
     * @param string $name
     * @return App_Model_Notification
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
     * Метод установки значения поля params
     *
     * @param string $params
     * @return App_Model_Notification
     */
    public function setParams($params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * Метод получения значения поля params
     *
     * @return string
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Установка массива параметров
     *
     * @param array $params
     */
    public function setArrayParams(array $params)
    {
        $this->setParams(json_encode($params));
    }

    /**
     * Получение массива параметров
     *
     * @return array
     */
    public function getArrayParams()
    {
        return json_decode($this->getParams(), true);
    }

    /**
     * Метод установки объекта модели доступа к
     * данным таблицы
     *
     * @param App_Model_Mapper_Notification $mapper
     * @return App_Model_Notification
     */
    public function setMapper(App_Model_Mapper_Notification $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Метод получения объекта модели доступа к
     * данным таблицы
     *
     * @return App_Model_Mapper_Notification
     */
    public function getMapper()
    {
        if (!$this->_mapper instanceof App_Model_Mapper_Notification) {
            $this->setMapper(new App_Model_Mapper_Notification());
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
     * @return App_Model_Notification
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
     * @return App_Model_Notification
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
     * @return App_Model_Notification[]
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

    /**
     * Получение данных объекта в строке
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}

