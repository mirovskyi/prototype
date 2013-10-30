<?php

/**
 * ORM модель таблицы user_info
 */
class App_Model_UserInfo
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
     * Поле таблицы, login
     *
     * @var string
     */
    protected $_login = null;

    /**
     * Поле таблицы, passwd
     *
     * @var string
     */
    protected $_passwd = null;

    /**
     * Поле таблицы, salt
     *
     * @var string
     */
    protected $_salt = null;

    /**
     * Поле таблицы, name
     *
     * @var string
     */
    protected $_name = null;

    /**
     * Поле таблицы, email
     *
     * @var string
     */
    protected $_email = null;

    /**
     * Поле таблицы, phone
     *
     * @var string
     */
    protected $_phone = null;

    /**
     * Поле таблицы, balance_real
     *
     * @var integer
     */
    protected $_balanceReal = null;

    /**
     * Поле таблицы, balance_free
     *
     * @var integer
     */
    protected $_balanceFree = null;

    /**
     * Поле таблицы, country
     *
     * @var string
     */
    protected $_country = null;

    /**
     * Поле таблицы, birth
     *
     * @var string
     */
    protected $_birth = null;

    /**
     * Поле таблицы, sex
     *
     * @var string
     */
    protected $_sex = null;

    /**
     * Поле таблицы, lang
     *
     * @var string
     */
    protected $_lang = null;

    /**
     * Поле таблицы, mode
     *
     * @var boolean
     */
    protected $_mode = null;

    /**
     * Объект модели доступа к данным таблицы
     *
     * @var App_Model_Mapper_UserInfo
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
     * @return App_Model_UserInfo
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
     * @return App_Model_UserInfo
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
     * Метод установки значения поля login
     *
     * @param string $login
     * @return App_Model_UserInfo
     */
    public function setLogin($login)
    {
        $this->_login = $login;
        return $this;
    }

    /**
     * Метод получения значения поля login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * Метод установки значения поля passwd
     *
     * @param string $passwd
     * @return App_Model_UserInfo
     */
    public function setPasswd($passwd)
    {
        $this->_passwd = $passwd;
        return $this;
    }

    /**
     * Метод получения значения поля passwd
     *
     * @return string
     */
    public function getPasswd()
    {
        return $this->_passwd;
    }

    /**
     * Метод установки значения поля salt
     *
     * @param string $salt
     * @return App_Model_UserInfo
     */
    public function setSalt($salt)
    {
        $this->_salt = $salt;
        return $this;
    }

    /**
     * Метод получения значения поля salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->_salt;
    }

    /**
     * Получение хеша пароля
     *
     * @param string $password Пароль
     * @return string
     */
    public function initHashPassword($password)
    {
        //Создание хэша пароля с использованием соли
        $hash = md5(md5($password) . md5($this->getSalt()));
        //Отдаем хеш пароля с солью
        return $hash;
    }

    /**
     * Метод установки значения поля name
     *
     * @param string $name
     * @return App_Model_UserInfo
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
     * Метод установки значения поля email
     *
     * @param string $email
     * @return App_Model_UserInfo
     */
    public function setEmail($email)
    {
        $this->_email = $email;
        return $this;
    }

    /**
     * Метод получения значения поля email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Метод установки значения поля phone
     *
     * @param string $phone
     * @return App_Model_UserInfo
     */
    public function setPhone($phone)
    {
        $this->_phone = $phone;
        return $this;
    }

    /**
     * Метод получения значения поля phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->_phone;
    }

    /**
     * Метод установки значения поля balance_real
     *
     * @param integer $balanceReal
     * @return App_Model_UserInfo
     */
    public function setBalanceReal($balanceReal)
    {
        $this->_balanceReal = $balanceReal;
        return $this;
    }

    /**
     * Метод получения значения поля balance_real
     *
     * @return integer
     */
    public function getBalanceReal()
    {
        return $this->_balanceReal;
    }

    /**
     * Метод установки значения поля balance_free
     *
     * @param integer $balanceFree
     * @return App_Model_UserInfo
     */
    public function setBalanceFree($balanceFree)
    {
        $this->_balanceFree = $balanceFree;
        return $this;
    }

    /**
     * Метод получения значения поля balance_free
     *
     * @return integer
     */
    public function getBalanceFree()
    {
        return $this->_balanceFree;
    }

    /**
     * Метод установки значения поля country
     *
     * @param string $country
     * @return App_Model_UserInfo
     */
    public function setCountry($country)
    {
        $this->_country = $country;
        return $this;
    }

    /**
     * Метод получения значения поля country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->_country;
    }

    /**
     * Метод установки значения поля birth
     *
     * @param string $birth
     * @return App_Model_UserInfo
     */
    public function setBirth($birth)
    {
        $this->_birth = $birth;
        return $this;
    }

    /**
     * Метод получения значения поля birth
     *
     * @return string
     */
    public function getBirth()
    {
        return $this->_birth;
    }

    /**
     * Метод установки значения поля sex
     *
     * @param string $sex
     * @return App_Model_UserInfo
     */
    public function setSex($sex)
    {
        $this->_sex = $sex;
        return $this;
    }

    /**
     * Метод получения значения поля sex
     *
     * @return string
     */
    public function getSex()
    {
        return $this->_sex;
    }

    /**
     * Метод установки значения поля lang
     *
     * @param string $lang
     * @return App_Model_UserInfo
     */
    public function setLang($lang)
    {
        $this->_lang = $lang;
        return $this;
    }

    /**
     * Метод получения значения поля lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->_lang;
    }

    /**
     * Метод установки значения поля mode
     *
     * @param boolean $mode
     * @return App_Model_UserInfo
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;
        return $this;
    }

    /**
     * Метод получения значения поля mode
     *
     * @return boolean
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Метод установки объекта модели доступа к
     * данным таблицы
     *
     * @param App_Model_Mapper_UserInfo $mapper
     * @return App_Model_UserInfo
     */
    public function setMapper(App_Model_Mapper_UserInfo $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Метод получения объекта модели доступа к
     * данным таблицы
     *
     * @return App_Model_Mapper_UserInfo
     */
    public function getMapper()
    {
        if (!$this->_mapper instanceof App_Model_Mapper_UserInfo) {
            $this->setMapper(new App_Model_Mapper_UserInfo());
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
     * @return App_Model_UserInfo
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
     * @return App_Model_UserInfo
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
     * @return App_Model_UserInfo[]
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

