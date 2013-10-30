<?php

/**
 * Description of Orm
 *
 * @author aleksey
 */
class Console_Model_Generate_Orm 
{
    
    /**
     * Наименование таблицы
     * @var string
     */
    protected $_tableName;
    
    /**
     * Модель таблицы
     * @var Zend_Db_Table_Abstract 
     */
    protected $_dbTable;
    
    /**
     * Директория для создания ORM модели
     * @var string
     */
    protected $_modelDir;
    
    /**
     * Имя генерируемого класса модели
     * @var type 
     */
    protected $_className;
    
    
    /**
     * __construct
     * @param string $table
     * @param string $modelDirectory [optional]
     * @param string $className [optional]
     */
    public function __construct($table, $modelDirectory = null, $className = null)
    {
        $this->_tableName = $table;
        $this->setModelDirectory($modelDirectory)
             ->setClassName($className);
    }
    
    /**
     * Метод установки директории ORM моделей
     * @param strig $dir
     * @return Console_Model_Generate_Orm 
     */
    public function setModelDirectory($dir)
    {
        $this->_modelDir = $dir;
        return $this;
    }
    
    /**
     * Метод получения директории ORM моделей
     * @return string
     */
    public function getModelDirectory()
    {
        return $this->_modelDir;
    }
    
    /**
     * Метод установки имени класса модели
     * @param string $name 
     * @return Console_Model_Generate_Orm 
     */
    public function setClassName($name)
    {
        $this->_className = $name;
        return $this;
    }
    
    /**
     * Метод получения имени класса модели
     * @return string 
     */
    public function getClassName()
    {
        return $this->_className;
    }

    /**
     * Метод получения модели таблицы
     * @return Zend_Db_Table_Abstract
     */
    public function getDbTable()
    {
        if (!$this->_dbTable instanceof Zend_Db_Table_Abstract) {
            $this->_dbTable = new Zend_Db_Table($this->_tableName);
        }
        return $this->_dbTable;
    }
    
    /**
     * Метод создания файла класса ORM модели
     * @return boolean
     */
    public function createClass()
    {
        $class = new Console_Model_Generate_Orm_Class($this->getDbTable());
        //Генерация кода класса
        if ($class->generate($this->getClassName())) {
            //Определяем путь файла класса
            $filename = $this->getModelDirectory() . DIRECTORY_SEPARATOR
                        . $this->_classFilename() . '.php';
            if ($filename[0] != DIRECTORY_SEPARATOR) {
                $filename = getcwd() . DIRECTORY_SEPARATOR . $filename;
            }
            //Сохраняем файл класса
            return file_put_contents($filename, $class->getPHPFile()->generate());
        }
        return false;
    }
    
    /**
     * Метод создания файла мапера
     * @return boolean
     */
    public function createClassMapper()
    {
        $mapper = new Console_Model_Generate_Orm_ClassMapper($this->getDbTable());
        //Генерация кода класса мапера
        if ($mapper->generate($this->getClassName())) {
            //Определяем путь файла класса
            $filename = $this->getModelDirectory() . DIRECTORY_SEPARATOR
                        . 'mappers' . DIRECTORY_SEPARATOR . $this->_classFilename() . '.php';
            if ($filename[0] != DIRECTORY_SEPARATOR) {
                $filename = getcwd() . DIRECTORY_SEPARATOR . $filename;
            }
            //Сохраняем файл класса
            return file_put_contents($filename, $mapper->getPHPFile()->generate());
        }
        return false;
    }
    
    /**
     * Метод создания файла класса таблицы
     * @return boolean
     */
    public function createClassDb()
    {
        $dbClass = new Console_Model_Generate_Orm_ClassDb($this->getDbTable());
        //Генерация кода класса мапера
        if ($dbClass->generate($this->getClassName())) {
            //Определяем путь файла класса
            $filename = $this->getModelDirectory() . DIRECTORY_SEPARATOR
                        . 'DbTable' . DIRECTORY_SEPARATOR
                        . $this->_classFilename() . '.php';
            if ($filename[0] != DIRECTORY_SEPARATOR) {
                $filename = getcwd() . DIRECTORY_SEPARATOR . $filename;
            }
            //Сохраняем файл класса
            return file_put_contents($filename, $dbClass->getPHPFile()->generate());
        }
        return false;
    }
    
    /**
     * Метод получения имени файла класса
     * @return string
     */
    protected function _classFilename()
    {
        $filename = explode('_', $this->getClassName());
        $filename = $filename[count($filename) - 1];
        return $filename;
    }
}