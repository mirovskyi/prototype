<?php

/**
 * Description of Log
 *
 * @author aleksey
 */
class Core_Plugin_Log extends Core_Plugin_Abstract
{
    
    public function preHandle() 
    {
        try {
            $request = new Core_Protocol_Request_Http();

            //Проверка наличия сессии игры
            $request = $request->__toString();
            @file_put_contents(APPLICATION_PATH . '/../data/logs/debug/' . date('Y-m-d') . '.log', date('c') . ' - REQUEST: ' . $request . PHP_EOL, FILE_APPEND);
        } catch (Zend_Exception $e) {
            @file_put_contents(APPLICATION_PATH . '/../data/logs/debug/debug', $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
    
    public function postHandle() 
    {
        try {
            $response = $this->getServer()->getResponse()->__toString();
            @file_put_contents(APPLICATION_PATH . '/../data/logs/debug/' . date('Y-m-d') . '.log', date('c') . ' - RESPONSE:' . PHP_EOL . $response . PHP_EOL, FILE_APPEND);
        } catch (Zend_Exception $e) {
            @file_put_contents(APPLICATION_PATH . '/../data/logs/debug/debug', $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
    
}