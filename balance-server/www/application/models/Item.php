<?php

/**
 * ORM модель таблицы items
 */
class App_Model_Item
{

    /**
     * Наименвание товара "фишки"
     */
    const CHIPS = 'chips';

    /**
     * Поле таблицы, id
     *
     * @var integer
     */
    protected $_id = null;

    /**
     * Поле таблицы, name
     *
     * @var string
     */
    protected $_name = null;

    /**
     * Поле таблицы, title
     *
     * @var string
     */
    protected $_title = null;

    /**
     * Поле таблицы, active
     *
     * @var boolean
     */
    protected $_active = null;

    /**
     * Поле таблицы, description
     *
     * @var string
     */
    protected $_description = null;

    /**
     * Объект модели доступа к данным таблицы
     *
     * @var App_Model_Mapper_Item
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
     * @return App_Model_Item
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
     * Метод установки значения поля name
     *
     * @param string $name
     * @return App_Model_Item
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
     * Метод установки значения поля title
     *
     * @param string $title
     * @return App_Model_Item
     */
    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    /**
     * Метод получения значения поля title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Метод установки значения поля active
     *
     * @param boolean $active
     * @return App_Model_Item
     */
    public function setActive($active)
    {
        $this->_active = $active;
        return $this;
    }

    /**
     * Метод получения значения поля active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->_active;
    }

    /**
     * Метод установки значения поля description
     *
     * @param string $description
     * @return App_Model_Item
     */
    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }

    /**
     * Метод получения значения поля description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Метод установки объекта модели доступа к
     * данным таблицы
     *
     * @param App_Model_Mapper_Item $mapper
     * @return App_Model_Item
     */
    public function setMapper(App_Model_Mapper_Item $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Метод получения объекта модели доступа к
     * данным таблицы
     *
     * @return App_Model_Mapper_Item
     */
    public function getMapper()
    {
        if (!$this->_mapper instanceof App_Model_Mapper_Item) {
            $this->setMapper(new App_Model_Mapper_Item());
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
     * @return App_Model_Item
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
     * @return App_Model_Item
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
     * @return App_Model_Item[]
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

