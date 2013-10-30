<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 30.08.12
 * Time: 12:00
 *
 * Сервис очистки истории игр
 */
class Cli_Service_Cleaner_History
{

    /**
     * Адапрет базы данных
     *
     * @var Zend_Db_Adapter_Abstract
     */
    private $_dbAdapter;

    /**
     * Список актуальных дат истории игр
     *
     * @var array
     */
    private $_actialDates;

    /**
     * Создание нового объекта сервиса
     *
     * @return Cli_Service_Cleaner_History
     */
    public function __construct()
    {
        //Проверка наличия ресурса базы данных
        if (Core_Server::getInstance()->getBootstrap()->hasResource('db')) {
            //Получение ресурса базы данных
            $resource = Core_Server::getInstance()->getBootstrap()->getResource('db');
            //Установка адаптера базы данных
            $this->setDbAdapter($resource->bootstrap());
        }
        //Установка списка актуальных дат истрии игр
        $this->_actialDates = $this->_getActualHistoryDates();
    }

    /**
     * Установка объекта адаптера базы данных
     *
     * @param Zend_Db_Adapter_Abstract $adapter
     * @return void
     */
    public function setDbAdapter(Zend_Db_Adapter_Abstract $adapter)
    {
        $this->_dbAdapter = $adapter;
    }

    /**
     * Получение объекта адаптера базы данных
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return $this->_dbAdapter;
    }

    /**
     * Удаление старых таблиц истории игр
     *
     * @return void
     */
    public function dropExpiredTables()
    {
        //Проверка актуальности каждой таблицы
        foreach($this->_getTableList() as $tableName) {
            //Проверка формата имени таблицы истории за сутки
            if (!preg_match('/^history_\d+$/', $tableName)) {
                continue;
            }
            //Проверка актуальности таблицы истории игр
            if (!$this->isActualTable($tableName)) {
                //Удаление таблицы из базы данных
                $this->getDbAdapter()->query('DROP TABLE ' . $tableName);
            }
        }
    }

    /**
     * Проверка актуальности табтицы истории игр
     *
     * @param string $tableName Имя таблицы истории игр
     *
     * @return bool
     */
    public function isActualTable($tableName)
    {
        //Получение даты из названия табтицы
        $tableDate = str_replace('history_', '', $tableName);
        //Проверка вхождения даты в список актуальных
        return in_array($tableDate, $this->_actialDates);
    }

    /**
     * Получение списка таблиц истории игр
     *
     * @return array
     */
    private function _getTableList()
    {
        //Возвращаем список таблиц истории игр
        return $this->getDbAdapter()->listTables();
    }

    /**
     * Получение списка актуальных дат истории игр
     *
     * @return array
     */
    private function _getActualHistoryDates()
    {
        //Список актуальных дат истории игр
        $dates = array();
        //Получение текущей даты
        $date = new DateTime();
        //Получение дат трех последних дней
        for ($i = 0; $i <= 2; $i ++) {
            $date->modify('-' . $i . ' days');
            $dates[] = $date->format('Ymd');
        }

        //Возвращаем список актуальных дат
        return $dates;
    }

}
