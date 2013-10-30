<?php

/**
 * ORM модель таблицы payments
 */
class App_Model_Payment
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
     * Поле таблицы, id_user
     *
     * @var integer
     */
    protected $_idUser = null;

    /**
     * Поле таблицы, id_shop_item
     *
     * @var integer
     */
    protected $_idShopItem = null;

    /**
     * Поле таблицы, amount
     *
     * @var integer
     */
    protected $_amount = null;

    /**
     * Поле таблицы, status
     *
     * @var string
     */
    protected $_status = null;

    /**
     * Поле таблицы, trans_date
     *
     * @var string
     */
    protected $_transDate = null;

    /**
     * Поле таблицы, strans_id
     *
     * @var string
     */
    protected $_stransId = null;

    /**
     * Объект модели доступа к данным таблицы
     *
     * @var App_Model_Mapper_Payment
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
     * @return App_Model_Payment
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
     * @return App_Model_Payment
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
     * Метод установки значения поля id_user
     *
     * @param integer $idUser
     * @return App_Model_Payment
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
     * Метод установки значения поля id_shop_item
     *
     * @param integer $idShopItem
     * @return App_Model_Payment
     */
    public function setIdShopItem($idShopItem)
    {
        $this->_idShopItem = $idShopItem;
        return $this;
    }

    /**
     * Метод получения значения поля id_shop_item
     *
     * @return integer
     */
    public function getIdShopItem()
    {
        return $this->_idShopItem;
    }

    /**
     * Метод установки значения поля amount
     *
     * @param integer $amount
     * @return App_Model_Payment
     */
    public function setAmount($amount)
    {
        $this->_amount = $amount;
        return $this;
    }

    /**
     * Метод получения значения поля amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * Метод установки значения поля status
     *
     * @param string $status
     * @return App_Model_Payment
     */
    public function setStatus($status)
    {
        $this->_status = $status;
        return $this;
    }

    /**
     * Метод получения значения поля status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Метод установки значения поля trans_date
     *
     * @param string $transDate
     * @return App_Model_Payment
     */
    public function setTransDate($transDate)
    {
        $this->_transDate = $transDate;
        return $this;
    }

    /**
     * Метод получения значения поля trans_date
     *
     * @return string
     */
    public function getTransDate()
    {
        return $this->_transDate;
    }

    /**
     * Метод установки значения поля strans_id
     *
     * @param string $stransId
     * @return App_Model_Payment
     */
    public function setStransId($stransId)
    {
        $this->_stransId = $stransId;
        return $this;
    }

    /**
     * Метод получения значения поля strans_id
     *
     * @return string
     */
    public function getStransId()
    {
        return $this->_stransId;
    }

    /**
     * Метод установки объекта модели доступа к
     * данным таблицы
     *
     * @param App_Model_Mapper_Payment $mapper
     * @return App_Model_Payment
     */
    public function setMapper(App_Model_Mapper_Payment $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Метод получения объекта модели доступа к
     * данным таблицы
     *
     * @return App_Model_Mapper_Payment
     */
    public function getMapper()
    {
        if (!$this->_mapper instanceof App_Model_Mapper_Payment) {
            $this->setMapper(new App_Model_Mapper_Payment());
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
     * @return App_Model_Payment
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
     * @return App_Model_Payment
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
     * @return App_Model_Payment[]
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

