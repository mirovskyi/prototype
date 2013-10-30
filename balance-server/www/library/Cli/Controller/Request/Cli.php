<?php

/**
 * Cli Request
 *
 * @author aleksey
 */
class Cli_Controller_Request_Cli extends Zend_Controller_Request_Simple
{
    
    /**
     * Объект аргументов 
     * @var Zend_Console_Getopt
     */
    protected $_console;
    
    /**
     * Метод получения имени запущенного скрипта скрипта
     * @return string
     */
    public function getScriptName()
    {
        if (isset ($_SERVER['argv'])) {
            $argv = $_SERVER['argv'];
            return preg_replace('/\..*$/', '', $argv[0]);
        }
        return false;
    }
    
    /**
     * Метод получения данных запроса
     * @param string $key
     * @param mixed $default
     * @return mixed 
     */
    public function getParam($key, $default = null) 
    {
        $key = (string) $key;
        //Проверка наличия ключа в пользовательских данных
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }
        //Проверка наличия ключа в данных запроса
        if ($this->getConsoleGetopt()->getOption($key) != null) {
            return $this->getConsoleGetopt()->getOption($key);
        } 
        //Возвращаем значение по умолчанию
        return $default;
    }
    
    /**
     * Метод проверки наличия ключа в данных запроса
     * @param string $key
     * @return boolean
     */
    public function hasParam($key)
    {
        $key = (string) $key;
        //Проверка наличия ключа в пользовательских данных
        if (isset($this->_params[$key])) {
            return true;
        }
        //Проверка наличия ключа в данных запроса
        if ($this->getConsoleGetopt()->getOption($key) != null) {
            return true;
        }
        return false;
    }
    
    /**
     * Метод получения объекта аргументов
     * @return Zend_Console_Getopt 
     */
    public function getConsoleGetopt()
    {
        return $this->_console;
    }
    
    /**
     * Метод установки объекта аргументов
     * @param Zend_Console_Getopt $getopt
     * @return Cli_Controller_Request_Cli 
     */
    public function setConsoleGetopt(Zend_Console_Getopt $getopt)
    {
        $this->_console = $getopt;
        return $this;
    }
    
}