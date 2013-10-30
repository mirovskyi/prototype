<?php

/**
 * Description of Request
 *
 * @author aleksey
 */
class Console_Core_Request 
{
    
    /**
     * Действие по умолчанию
     */
    const DEFAULT_ACTION = 'index';
    
    /**
     * Объект работы с консоьными аргументами
     * @var Zend_Console_Getopt
     */
    protected $_getopt;
    
    /**
     * Параметры
     * @var array 
     */
    protected $_params = array();
    
    /**
     * __construct
     */
    public function __construct() 
    {
        $request = explode(':', $_SERVER['argv'][1]);
        $this->setCommand($request[0]);
        if (isset($request[1])) {
            $this->setAction($request[1]);
        } else {
            $this->setAction(self::DEFAULT_ACTION);
        }
    }
    
    /**
     * Метод установки объекта обоаботки консольного запроса
     * @param Zend_Console_Getopt $getopt
     * @return Console_Core_Request 
     */
    public function setGetopt(Zend_Console_Getopt $getopt)
    {
        $this->_getopt = $getopt;
        return $this;
    }
    
    /**
     * Метод получения объекта обоаботки консольного запроса
     * @return Zend_Console_Getopt 
     */
    public function getGetopt()
    {
        return $this->_getopt;
    }
    
    /**
     * Метод установки комманды
     * @param string $command
     * @return Console_Core_Request 
     */
    public function setCommand($command)
    {
        $this->setParam('command', $command);
        return $this;
    }
    
    /**
     * Метод получения комманды
     * @return string 
     */
    public function getCommand()
    {
        return $this->getParam('command');
    }
    
    /**
     * Метод установки действия
     * @param string $action
     * @return Console_Core_Request 
     */
    public function setAction($action)
    {
        $this->setParam('action', $action);
        return $this;
    }
    
    /** 
     * Метод полуения комманды
     * @return string 
     */
    public function getAction()
    {
        return $this->getParam('action');
    }
    
    /**
     * Метод установки параметра запроса
     * @param string $name
     * @param mixed $value
     * @return Console_Core_Request 
     */
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
        return $this;
    }
    
    /** 
     * Метод проверки наличия параметра запроса
     * @param string $name
     * @return boolean
     */
    public function hasParam($name)
    {
        if (isset($this->_params[$name])) {
            return true;
        }
        if ($this->getGetopt() instanceof Zend_Console_Getopt &&
            $this->getGetopt()->getOption($name)) {
            return true;
        }
        return false;
    }
    
    /** 
     * Метод получения параметра запроса
     * @param string $name
     * @param mixed $default
     * @return boolean
     */
    public function getParam($name, $default = null)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }
        if ($this->getGetopt() instanceof Zend_Console_Getopt &&
            $this->getGetopt()->getOption($name)) {
            return $this->getGetopt()->getOption($name);
        }
        return $default;
    }
    
    /**
     * Метод получения списка параметров запроса
     * @return array
     */
    public function getParams()
    {
        if ($this->getGetopt() instanceof Zend_Console_Getopt) {
            return array_merge($this->getGetopt()->toArray(), $this->_params);
        }
        return $this->_params;
    }
    
}