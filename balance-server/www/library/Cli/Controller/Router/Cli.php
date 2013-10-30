<?php

/**
 * Cli router
 *
 * @author aleksey
 */
class Cli_Controller_Router_Cli extends Zend_Controller_Router_Abstract 
{
    
    /**
     * Модуль по умолчанию
     */
    const DEFAULT_CONTROLLER = 'cli';
    
    /**
     * Действие по умолчанию
     */
    const DEFAULT_ACTION = 'index';

    /**
     * Processes a request and sets its controller and action.  If
     * no route was possible, an exception is thrown.
     *
     * @param  Zend_Controller_Request_Abstract
     * @throws Zend_Controller_Router_Exception
     * @return Zend_Controller_Request_Abstract|boolean
     */
    public function route(Zend_Controller_Request_Abstract $request) 
    {
        try {
            //Создание объекта cli опций
            $console = new Zend_Console_Getopt(array(
                'action|a-s' => 'Наименование действия (index - по умолчанию)',
                'kill|k' => 'Флаг принудительного завершения процесса, если он еще запущен',
                'help|h' => 'Help'
            ));
            //Пасинг введенных в консоли опций 
            $console->parse();
            //Отображение хелпа
            if ($console->getOption('h')) {
                echo $console->getUsageMessage();
                die;
            }
            //Определение маршрута
            $cotroller = self::DEFAULT_CONTROLLER;
            if ($console->getOption('a')) {
                $action = $console->getOption('a');
            } else {
                $action = self::DEFAULT_ACTION;
            }
            //Установка данных маршрута в объект запроса
            $request->setControllerName($cotroller)
                    ->setActionName($action)
                    ->setConsoleGetopt($console);
        } catch (Zend_Console_Getopt_Exception $e) {
            //Переход на обработчик ошибок
            $request->setControllerName(self::DEFAULT_CONTROLLER)
                    ->setActionName('error');
            $this->getFrontController()->getResponse()->setException($e);
            return $request;
        }
        return $request;
    }
    
    public function assemble($userParams, $name = null, $reset = false, $encode = true)
    {}
    
}