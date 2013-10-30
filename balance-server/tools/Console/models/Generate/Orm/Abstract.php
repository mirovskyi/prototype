<?php

/**
 * Description of Abstract
 *
 * @author aleksey
 */
abstract class Console_Model_Generate_Orm_Abstract 
{
    
    /**
     * Имя генерируемого класса
     * @var string 
     */
    protected $_className;
    
    /**
     * Модель таблицы
     * @var Zend_Db_Table_Abstract 
     */
    protected $_dbTable;
    
    /**
     * Код класса
     * @var Zend_CodeGenerator_Php_File
     */
    protected $_phpFile;
    
    /**
     * __construct
     * @param Zend_Db_Table_Abstract $dbTable 
     */
    public function __construct(Zend_Db_Table_Abstract $dbTable)
    {
        $this->setDbTable($dbTable);
    }
    
    /**
     * Метод установки модели таблицы
     * @param Zend_Db_Table_Abstract $dbTable
     * @return Console_Model_Generate_Orm_Abstract 
     */
    public function setDbTable(Zend_Db_Table_Abstract $dbTable)
    {
        $this->_dbTable = $dbTable;
        return $this;
    }
    
    /**
     * Метод получения модели таблицы
     * @return Zend_Db_Table_Abstract 
     */
    public function getDbTable()
    {
        return $this->_dbTable;
    }
    
    /**
     * Метод получения объекта PHP фйла
     * @return Zend_CodeGenerator_Php_File
     */
    public function getPHPFile()
    {
        if (!$this->_phpFile instanceof Zend_CodeGenerator_Php_File) {
            $this->_phpFile = new Zend_CodeGenerator_Php_File();
        }
        return $this->_phpFile;
    }

    /**
     * Метод генерации кода ORM модели
     * @param string $className Имя класса модели
     */
    abstract public function generate($className);
    
    /**
     * Метод конвертации типа данных базы данных в тип данных PHP
     * @param string $dbType
     * @return string 
     */
    protected function _typeOf($dbType)
    {
        switch ($dbType) {
            case 'varchar' :
            case 'text' :
            case 'decimal' :
            case 'enum' : return 'string';
            case 'int' : return 'integer';
            case 'bool' :
            case 'tinyint' : return 'boolean';
            default : return 'string';
        }
    }
    
    /**
     * Метод получения имени поля/метода класса из названия поля таблицы
     * @param string $fieldName
     * @return string
     */
    protected function _camelFieldName($fieldName)
    {
        $nameParts = explode('_', $fieldName);
        $result = strtolower($nameParts[0]);
        if (count($nameParts) > 1) {
            unset($nameParts[0]);
            foreach($nameParts as $part) {
                $result .= ucfirst(strtolower($part));
            }
        }
        return $result;
    }
    
    /**
     * Метод получения имени класса маппера
     * @return string
     */
    protected function _mapperClassName()
    {
        //return $this->_className . 'Mapper';
        //Вставка в имя класса директорию 'mapper' перед названием имени файла класса
        $paths = explode('_', $this->_className);
        $fileName = array_pop($paths);
        array_push($paths, 'Mapper', $fileName);

        return implode('_', $paths);
    }
    
    /**
     * Метод получения имени класса модели таблицы
     * @return string
     */
    protected function _dbClassName()
    {
        $name = explode('_', $this->_className);
        $classFile = array_pop($name);
        array_push($name, 'DbTable', $classFile);
        return implode('_', $name);
    }
    
}