<?php

/**
 * Log for render messages in cli (for development mode)
 *
 * @author aleksey
 */
class Cli_Log 
{
    
    const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages
    
    /**
     * @var array of priorities where the keys are the
     * priority numbers and the values are the priority names
     */
    protected $_priorities = array();
    
    /**
     * __construct
     * 
     * @return void
     */
    public function __construct() 
    {
        $r = new ReflectionClass($this);
        $this->_priorities = array_flip($r->getConstants());
    }
    
    /**
     * Undefined method handler allows a shortcut:
     *   $log->priorityName('message')
     *     instead of
     *   $log->log('message', Zend_Log::PRIORITY_NAME)
     *
     * @param  string  $method  priority name
     * @param  string  $params  message to log
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __call($method, $params)
    {
        $priority = strtoupper($method);
        if (($priority = array_search($priority, $this->_priorities)) !== false) {
            $this->log(array_shift($params), $priority);
        } else {
            /** @see Zend_Log_Exception */
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Bad log priority');
        }
    }
    
    /**
     * Метод вывода сообщения лога на экран
     * @param string $message
     * @param integer $priority 
     */
    public function log($message, $priority)
    {
        if (! isset($this->_priorities[$priority])) {
            /** @see Zend_Log_Exception */
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Bad log priority');
        }
        //Вывод лога на экран
        echo 'LOG::' . $this->_priorities[$priority] . ' - ' . $message . PHP_EOL;
        //Вывод буфера
        ob_flush();
    }
    
}