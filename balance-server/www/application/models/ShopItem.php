<?php

/**
 * ORM модель таблицы shop_items
 */
class App_Model_ShopItem
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
     * Поле таблицы, id_item
     *
     * @var integer
     */
    protected $_idItem = null;

    /**
     * Поле таблицы, chips
     *
     * @var integer
     */
    protected $_chips = null;

    /**
     * Поле таблицы, money
     *
     * @var integer
     */
    protected $_money = null;

    /**
     * Поле таблицы, item_count
     *
     * @var integer
     */
    protected $_itemCount = null;

    /**
     * Поле таблицы, lifetime
     *
     * @var integer
     */
    protected $_lifetime = null;

    /**
     * Объект модели доступа к данным таблицы
     *
     * @var App_Model_Mapper_ShopItem
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
     * @return App_Model_ShopItem
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
     * @return App_Model_ShopItem
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
     * Метод установки значения поля id_item
     *
     * @param integer $idItem
     * @return App_Model_ShopItem
     */
    public function setIdItem($idItem)
    {
        $this->_idItem = $idItem;
        return $this;
    }

    /**
     * Метод получения значения поля id_item
     *
     * @return integer
     */
    public function getIdItem()
    {
        return $this->_idItem;
    }

    /**
     * Метод установки значения поля chips
     *
     * @param integer $chips
     * @return App_Model_ShopItem
     */
    public function setChips($chips)
    {
        $this->_chips = $chips;
        return $this;
    }

    /**
     * Метод получения значения поля chips
     *
     * @return integer
     */
    public function getChips()
    {
        return $this->_chips;
    }

    /**
     * Метод установки значения поля money
     *
     * @param integer $money
     * @return App_Model_ShopItem
     */
    public function setMoney($money)
    {
        $this->_money = $money;
        return $this;
    }

    /**
     * Метод получения значения поля money
     *
     * @return integer
     */
    public function getMoney()
    {
        return $this->_money;
    }

    /**
     * Метод установки значения поля item_count
     *
     * @param integer $itemCount
     * @return App_Model_ShopItem
     */
    public function setItemCount($itemCount)
    {
        $this->_itemCount = $itemCount;
        return $this;
    }

    /**
     * Метод получения значения поля item_count
     *
     * @return integer
     */
    public function getItemCount()
    {
        return $this->_itemCount;
    }

    /**
     * Метод установки значения поля lifetime
     *
     * @param integer $lifetime
     * @return App_Model_ShopItem
     */
    public function setLifetime($lifetime)
    {
        $this->_lifetime = $lifetime;
        return $this;
    }

    /**
     * Метод получения значения поля lifetime
     *
     * @return integer
     */
    public function getLifetime()
    {
        return $this->_lifetime;
    }

    /**
     * Метод установки объекта модели доступа к
     * данным таблицы
     *
     * @param App_Model_Mapper_ShopItem $mapper
     * @return App_Model_ShopItem
     */
    public function setMapper(App_Model_Mapper_ShopItem $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Метод получения объекта модели доступа к
     * данным таблицы
     *
     * @return App_Model_Mapper_ShopItem
     */
    public function getMapper()
    {
        if (!$this->_mapper instanceof App_Model_Mapper_ShopItem) {
            $this->setMapper(new App_Model_Mapper_ShopItem());
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
     * @return App_Model_ShopItem
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
     * @return App_Model_ShopItem
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
     * @return App_Model_ShopItem[]
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

