<?php


class App_Model_DbTable_Abstract extends Zend_Db_Table_Abstract
{

    public function __construct($config = array())
    {
        //Проверка наличия адаптера БД
        if (null === $this->getDefaultAdapter()) {
            //Инициализация ресурса БД адаптера
            $bootstrap = Core_Server::getInstance()->getBootstrap();
            if ($bootstrap->hasResource('db')) {
                $this->setDefaultAdapter($bootstrap->getResource('db')->bootstrap());
            }
        }

        parent::__construct($config);
    }

}
