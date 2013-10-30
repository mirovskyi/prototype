<?php

/**
 * Description of ClassDb
 *
 * @author aleksey
 */
class Console_Model_Generate_Orm_ClassDb extends Console_Model_Generate_Orm_Abstract 
{
    
    public function generate($className)
    {
        //Установка имени класса
        $this->_className = $className;
        //Создание код класса таблицы
        try {
            $class = new Zend_CodeGenerator_Php_Class(array(
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'shortDescription' => 'Класс таблицы ' . $this->getDbTable()->info('name')
                )),
                'name' => $this->_dbClassName(),
                'properties' => array(
                    array(
                        'name' => '_name',
                        'visibility' => 'protected',
                        'defaultValue' => $this->getDbTable()->info('name')
                    )
                )
            ));
            $class->setExtendedClass('Zend_Db_Table_Abstract');
        } catch (Zend_CodeGenerator_Exception $e) {
            return false;
        }
        //Установка кода класса в PHP файл
        $this->getPHPFile()->setClass($class);
        return true;
    }
    
}