<?php

/**
 * ORM модель таблицы user_experience
 */
class App_Model_UserExperience
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
     * Поле таблицы, id_game
     *
     * @var integer
     */
    protected $_idGame = null;

    /**
     * Поле таблицы, number
     *
     * @var integer
     */
    protected $_number = null;

    /**
     * Поле таблицы, win
     *
     * @var integer
     */
    protected $_win = null;

    /**
     * Объект модели доступа к данным таблицы
     *
     * @var App_Model_Mapper_UserExperience
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
     * @return App_Model_UserExperience
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
     * @return App_Model_UserExperience
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
     * Метод установки значения поля id_game
     *
     * @param integer $idGame
     * @return App_Model_UserExperience
     */
    public function setIdGame($idGame)
    {
        $this->_idGame = $idGame;
        return $this;
    }

    /**
     * Метод получения значения поля id_game
     *
     * @return integer
     */
    public function getIdGame()
    {
        return $this->_idGame;
    }

    /**
     * Метод установки значения поля number
     *
     * @param integer $number
     * @return App_Model_UserExperience
     */
    public function setNumber($number)
    {
        $this->_number = $number;
        return $this;
    }

    /**
     * Метод получения значения поля number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->_number;
    }

    /**
     * Метод установки значения поля win
     *
     * @param integer $win
     * @return App_Model_UserExperience
     */
    public function setWin($win)
    {
        $this->_win = $win;
        return $this;
    }

    /**
     * Метод получения значения поля win
     *
     * @return integer
     */
    public function getWin()
    {
        return $this->_win;
    }

    /**
     * Метод установки объекта модели доступа к
     * данным таблицы
     *
     * @param App_Model_Mapper_UserExperience $mapper
     * @return App_Model_UserExperience
     */
    public function setMapper(App_Model_Mapper_UserExperience $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Метод получения объекта модели доступа к
     * данным таблицы
     *
     * @return App_Model_Mapper_UserExperience
     */
    public function getMapper()
    {
        if (!$this->_mapper instanceof App_Model_Mapper_UserExperience) {
            $this->setMapper(new App_Model_Mapper_UserExperience());
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
     * @return App_Model_UserExperience
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
     * @return App_Model_UserExperience
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
     * @return App_Model_UserExperience[]
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

