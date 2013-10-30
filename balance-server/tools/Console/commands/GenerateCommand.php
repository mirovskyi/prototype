<?php

/**
 * Description of TestCommand
 *
 * @author aleksey
 */
class Console_GenerateCommand extends Console_Core_Command
{

    
    public function ormAction()
    {
        if ($this->_hasParam('table-name') &&
            $this->_hasParam('path') &&
            $this->_hasParam('class-name')) {
            //Создание модели генерации ORM
            $orm = new Console_Model_Generate_Orm($this->_getParam('table-name'), 
                                                  $this->_getParam('path'), 
                                                  $this->_getParam('class-name'));
            //Создание файла класса
            if ($orm->createClass()) {
                $this->getResponse()->appendBody('ORM class successful created' . PHP_EOL);
            } else {
                $this->getResponse()->appendBody('An eror occurred while create ORM class' . PHP_EOL);
            }
            //Создание файла мапера
            if ($orm->createClassMapper()) {
                $this->getResponse()->appendBody('ORM mapper class successful created' . PHP_EOL);
            } else {
                $this->getResponse()->appendBody('An eror occurred while create ORM mapper class' . PHP_EOL);
            }
            //Создание файла класса таблицы
            if ($orm->createClassDb()) {
                $this->getResponse()->appendBody('Database table class successful created' . PHP_EOL);
            } else {
                $this->getResponse()->appendBody('An eror occurred while create database class' . PHP_EOL);
            }
        } elseif ($this->_hasParam('help')) {
            $this->getResponse()->setBody($this->getRequest()->getGetopt()->getUsageMessage());
        } else {
            $this->getResponse()->setBody('Invalid request params');
        }
    }
    
}