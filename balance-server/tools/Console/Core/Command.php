<?php

/**
 * Description of Command
 *
 * @author aleksey
 */
class Console_Core_Command 
{
    
    /**
     * Объект запроса
     * @var Console_Core_Request
     */
    protected $_request;
    
    /**
     * Объект ответа
     * @var Zend_Controller_Response_Cli
     */
    protected $_response;
    
    
    public function __construct()
    {
        $this->_request = Console_Core_Front::getInstance()->getRequest();
        $this->_response = Console_Core_Front::getInstance()->getResponse();
        $this->_initArgvRules();
    }
    
    /**
     * Init
     */
    public function init() 
    {}
    
    /**
     * Метод получения объекта запроса
     * @return Console_Core_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * Метод установки объекта ответа
     * @return Zend_Controller_Response_Cli
     */
    public function getResponse()
    {
        return $this->_response;
    }
    
    /**
     * Метод получения параметра запроса
     * @param string $name
     * @param mixed $default
     * @return mixed 
     */
    protected function _getParam($name, $default = null)
    {
        return $this->getRequest()->getParam($name, $default);
    }
    
    /**
     * Метод проверки наличия парметра в запросе
     * @param string $name
     * @return boolean 
     */
    protected function _hasParam($name)
    {
        return $this->getRequest()->hasParam($name);
    }
    
    /**
     * Метод установки параметра запроса
     * @param string $name
     * @param mixed $value
     * @return Console_Core_Command 
     */
    protected function _setParam($name, $value) 
    {
        $this->getRequest()->setParam($name, $value);
        return $this;
    }
    
    /**
     * Метод получения списка параметров запроса
     * @return array
     */
    protected function _getAllParams()
    {
        return $this->getRequest()->getParams();
    }
    
    /**
     * Метод установки правил парсинга аргументов коммандной сироки
     */
    protected function _initArgvRules()
    {
        //Проверка наличия настройки правил
        $options = Console_Core_Front::getInstance()->bootstrap()
                                                    ->getOption('rules');
        if ($options instanceof Zend_Config) {
            //Получаем правила для конкретной комманды
            $command = $this->getRequest()->getCommand();
            //Проверка наличия правил
            if (isset($options->$command)) {
                $rules = $options->$command->toArray();
                //Проверка наличия правил для действия
                $action = $this->getRequest()->getAction();
                if (isset($options->$command->$action)) {
                    $rules = $options->$command->$action->toArray();
                }
                //Формирование массива правил
                $argvRules = array();
                foreach($rules as $rule) {
                    if (isset($rule['key']) && isset($rule['value'])) {
                        $argvRules[$rule['key']] = $rule['value'];
                    }
                }
                if (count($argvRules)) {
                    $this->getRequest()->setGetopt(new Zend_Console_Getopt($argvRules));
                }
            }
        }
        
    }
    
}