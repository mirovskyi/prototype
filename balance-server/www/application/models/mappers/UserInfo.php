<?php

/**
 * Класс реализующий слой доступа к данным БД
 * в ORM модели, для таблицы user_info
 */
class App_Model_Mapper_UserInfo
{

    /**
     * Объект модели таблицы
     *
     * @var Zend_Db_Table_Abstract
     */
    protected $_dbTable = null;

    /**
     * Метод установки объекта модели таблицы
     *
     * @param string|Zend_Db_Table_Abstract $dbTable Объект модели
     * таблицы ИЛИ имя класса модели таблицы
     * @throws Exception
     * @return App_Model_Mapper_UserInfo
     */
    public function setDbTable($dbTable)
    {
        if ($dbTable instanceof Zend_Db_Table_Abstract) {
            $this->_dbTable = $dbTable;
        } elseif (is_string($dbTable)) {
            if (class_exists($dbTable)) {
                $this->_dbTable = new $dbTable();
            } else {
                throw new Exception('Class ' . $dbTable . ' does not exist');
            }
        }
        return $this;
    }

    /**
     * Метод получения объекта модели таблицы
     *
     * @return Zend_Db_Table_Abstract
     */
    public function getDbTable()
    {
        if (!$this->_dbTable instanceof Zend_Db_Table_Abstract) {
            $this->setDbTable('App_Model_DbTable_UserInfo');
        }
        return $this->_dbTable;
    }

    /**
     * Метод сохранения объекта в БД
     *
     * @param App_Model_UserInfo $entity Объект сущности
     * @return integer|boolean
     */
    public function save(App_Model_UserInfo $entity)
    {
        $data = array();
        if ($entity->getId() !== null) {
            $data['id'] = $entity->getId();
        }
        if ($entity->getIdUser() !== null) {
            $data['id_user'] = $entity->getIdUser();
        }
        if ($entity->getLogin() !== null) {
            $data['login'] = $entity->getLogin();
        }
        if ($entity->getPasswd() !== null) {
            $data['passwd'] = $entity->getPasswd();
        }
        if ($entity->getSalt() !== null) {
            $data['salt'] = $entity->getSalt();
        }
        if ($entity->getName() !== null) {
            $data['name'] = $entity->getName();
        }
        if ($entity->getEmail() !== null) {
            $data['email'] = $entity->getEmail();
        }
        if ($entity->getPhone() !== null) {
            $data['phone'] = $entity->getPhone();
        }
        if ($entity->getBalanceReal() !== null) {
            $data['balance_real'] = $entity->getBalanceReal();
        }
        if ($entity->getBalanceFree() !== null) {
            $data['balance_free'] = $entity->getBalanceFree();
        }
        if ($entity->getCountry() !== null) {
            $data['country'] = $entity->getCountry();
        }
        if ($entity->getBirth() !== null) {
            $data['birth'] = $entity->getBirth();
        }
        if ($entity->getSex() !== null) {
            $data['sex'] = $entity->getSex();
        }
        if ($entity->getLang() !== null) {
            $data['lang'] = $entity->getLang();
        }
        if ($entity->getMode() !== null) {
            $data['mode'] = $entity->getMode();
        }
        if (!isset($data['id'])) {
            $id = $this->getDbTable()->insert($data);
            if ($id != null) {
                $entity->setId($id);
            }
            return $id;
        } else {
            return $this->getDbTable()->update($data, array('id = ' . $entity->getId()));
        }
    }

    /**
     * Метод получения объекта выборки
     *
     * @return Zend_Db_Select
     */
    public function select()
    {
        return $this->getDbTable()->select();
    }

    /**
     * Метод поиска записи по первичному ключу
     *
     * @param App_Model_UserInfo $entity Объект сущности
     * @param integer|string $id Значение первичного ключа
     */
    public function find(App_Model_UserInfo $entity, $id)
    {
        $result = $this->getDbTable()->find($id);
        if ($result->count() > 0) {
            $row = $result->current();
        } else {
            return;
        }
        $entity->setId($row->id);
        $entity->setIdUser($row->id_user);
        $entity->setLogin($row->login);
        $entity->setPasswd($row->passwd);
        $entity->setSalt($row->salt);
        $entity->setName($row->name);
        $entity->setEmail($row->email);
        $entity->setPhone($row->phone);
        $entity->setBalanceReal($row->balance_real);
        $entity->setBalanceFree($row->balance_free);
        $entity->setCountry($row->country);
        $entity->setBirth($row->birth);
        $entity->setSex($row->sex);
        $entity->setLang($row->lang);
        $entity->setMode($row->mode);
    }

    /**
     * Метод поиска записи в БД
     *
     * @param App_Model_UserInfo $entity Объект сущности
     * @param string|array|Zend_Db_Table_Select $where Условия поиска
     * @param string|array $order Условия сортировки
     */
    public function fetchRow(App_Model_UserInfo $entity, $where = null, $order = null)
    {
        $result = $this->getDbTable()->fetchRow($where, $order);
        if (count($result) > 0) {
            $row = $result;
        } else {
            return;
        }
        $entity->setId($row->id);
        $entity->setIdUser($row->id_user);
        $entity->setLogin($row->login);
        $entity->setPasswd($row->passwd);
        $entity->setSalt($row->salt);
        $entity->setName($row->name);
        $entity->setEmail($row->email);
        $entity->setPhone($row->phone);
        $entity->setBalanceReal($row->balance_real);
        $entity->setBalanceFree($row->balance_free);
        $entity->setCountry($row->country);
        $entity->setBirth($row->birth);
        $entity->setSex($row->sex);
        $entity->setLang($row->lang);
        $entity->setMode($row->mode);
    }

    /**
     * Метод выборки записей в БД по заданному
     * условию
     *
     * @param string|array|Zend_Db_Table_Select $where Условия выборки
     * @param string|array $order Условие сортировки
     * @param integer $count Количество записей в
     * результате
     * @param integer $offset Номер записи, с которой ведется
     * поиск
     * @return App_Model_UserInfo[]
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $entities = array();
        $result = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
        if ($result->count() > 0) {
            foreach($result as $row) {
                $entity = new App_Model_UserInfo();
                $entity->setId($row->id);
                $entity->setIdUser($row->id_user);
                $entity->setLogin($row->login);
                $entity->setPasswd($row->passwd);
                $entity->setSalt($row->salt);
                $entity->setName($row->name);
                $entity->setEmail($row->email);
                $entity->setPhone($row->phone);
                $entity->setBalanceReal($row->balance_real);
                $entity->setBalanceFree($row->balance_free);
                $entity->setCountry($row->country);
                $entity->setBirth($row->birth);
                $entity->setSex($row->sex);
                $entity->setLang($row->lang);
                $entity->setMode($row->mode);
                $entity->setMapper($this);
                $entities[] = $entity;
            }
        }
        return $entities;
    }

    /**
     * Метод удаления записей по указанному
     * условию выборки
     *
     * @param string|array $where Условия выборки записей для
     * удаления
     * @return integer
     */
    public function delete($where)
    {
        return $this->getDbTable()->delete($where);
    }


}

