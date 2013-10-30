<?php

/**
 * Класс реализующий слой доступа к данным БД
 * в ORM модели, для таблицы gifts
 */
class App_Model_Mapper_Gift
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
     * @return App_Model_Mapper_Gift
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
            $this->setDbTable('App_Model_DbTable_Gift');
        }
        return $this->_dbTable;
    }

    /**
     * Метод сохранения объекта в БД
     *
     * @param App_Model_Gift $entity Объект сущности
     * @return integer|boolean
     */
    public function save(App_Model_Gift $entity)
    {
        $data = array();
        if ($entity->getId() !== null) {
            $data['id'] = $entity->getId();
        }
        if ($entity->getIdService() !== null) {
            $data['id_service'] = $entity->getIdService();
        }
        if ($entity->getIdUserFrom() !== null) {
            $data['id_user_from'] = $entity->getIdUserFrom();
        }
        if ($entity->getIdUserTo() !== null) {
            $data['id_user_to'] = $entity->getIdUserTo();
        }
        if ($entity->getName() !== null) {
            $data['name'] = $entity->getName();
        }
        if ($entity->getCreateDate() !== null) {
            $data['create_date'] = $entity->getCreateDate();
        }
        if ($entity->getIsReceived() !== null) {
            $data['is_received'] = $entity->getIsReceived();
        }
        if ($entity->getReceivedDate() !== null) {
            $data['received_date'] = $entity->getReceivedDate();
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
     * @param App_Model_Gift $entity Объект сущности
     * @param integer|string $id Значение первичного ключа
     */
    public function find(App_Model_Gift $entity, $id)
    {
        $result = $this->getDbTable()->find($id);
        if ($result->count() > 0) {
            $row = $result->current();
        } else {
            return;
        }
        $entity->setId($row->id);
        $entity->setIdService($row->id_service);
        $entity->setIdUserFrom($row->id_user_from);
        $entity->setIdUserTo($row->id_user_to);
        $entity->setName($row->name);
        $entity->setCreateDate($row->create_date);
        $entity->setIsReceived($row->is_received);
        $entity->setReceivedDate($row->received_date);
    }

    /**
     * Метод поиска записи в БД
     *
     * @param App_Model_Gift $entity Объект сущности
     * @param string|array|Zend_Db_Table_Select $where Условия поиска
     * @param string|array $order Условия сортировки
     */
    public function fetchRow(App_Model_Gift $entity, $where = null, $order = null)
    {
        $result = $this->getDbTable()->fetchRow($where, $order);
        if (count($result) > 0) {
            $row = $result;
        } else {
            return;
        }
        $entity->setId($row->id);
        $entity->setIdService($row->id_service);
        $entity->setIdUserFrom($row->id_user_from);
        $entity->setIdUserTo($row->id_user_to);
        $entity->setName($row->name);
        $entity->setCreateDate($row->create_date);
        $entity->setIsReceived($row->is_received);
        $entity->setReceivedDate($row->received_date);
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
     * @return App_Model_Gift[]
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $entities = array();
        $result = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
        if ($result->count() > 0) {
            foreach($result as $row) {
                $entity = new App_Model_Gift();
                $entity->setId($row->id);
                $entity->setIdService($row->id_service);
                $entity->setIdUserFrom($row->id_user_from);
                $entity->setIdUserTo($row->id_user_to);
                $entity->setName($row->name);
                $entity->setCreateDate($row->create_date);
                $entity->setIsReceived($row->is_received);
                $entity->setReceivedDate($row->received_date);
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

