<?php

/**
 * Description of Front
 *
 * @author aleksey
 */
class Console_Core_Front 
{
    
    /**
     * Экземпляр класса
     * @var Console_Front
     */
    protected static $_instance;
    
    /**
     * Bootstrap
     * @var Console_Bootstrap 
     */
    protected $_bootstrap;
    
    /**
     * Объект запроса
     * @var Console_Core_Request
     */
    protected $_request;
    
    /**
     * Объект ответа
     * @var Console_Core_Response
     */
    protected $_response;
    
    /**
     * Метод получения экземпляра класса
     * @return Console_Core_Front
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * __construct
     */
    protected function __construct() 
    {
        $this->_request = new Console_Core_Request();
        $this->_response = new Zend_Controller_Response_Cli();
    }
    
    /**
     * Метод установки объекта запроса
     * @param Console_Core_Request $request
     * @return Console_Core_Front 
     */
    public function setRequest(Console_Core_Request $request)
    {
        $this->_request = $request;
        return $this;
    }
    
    /**
     * Метод получения объекта запроса
     * @return type 
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * Метод установки объекта ответа
     * @param Zend_Controller_Response_Cli $response
     * @return Console_Core_Front 
     */
    public function setResponse(Zend_Controller_Response_Cli $response)
    {
        $this->_response = $response;
        return $this;
    }
    
    /**
     * Метод получения объекта ответа
     * @return type 
     */
    public function getResponse()
    {
        return $this->_response;
    }
    
    /**
     * Метод получения/установки бутстрапа
     * @param Console_Bootstrap $bootstrap [optional]
     * @return Console_Core_Front 
     */
    public function bootstrap($bootstrap = null)
    {
        if ($bootstrap instanceof Console_Bootstrap) {
            $this->_bootstrap = $bootstrap;
            return $this;
        }
        return $this->_bootstrap;
    }
    
    /**
     * Диспетчиризация
     */
    public function dispatch()
    {
        //Определяем контроллер для инициализации
        $command = $this->getRequest()->getCommand();
        $commandClass = 'Console_' . ucfirst($command) . 'Command';
        if (!class_exists($commandClass)) {
            throw new Exception('Execute command class does not exist');
        }
        //Создание объекта контроллера
        $controller = new $commandClass();
        //Проверка наличия действия
        $action = $this->getRequest()->getAction() . 'Action';
        if (!method_exists($controller, $action)) {
            throw new Exception('Action does not exist');
        }
        //Дрспетчиризация
        try {
            //Инициализация контроллера
            $controller->init();
            //Запуск действия
            $controller->$action();
        } catch (Exception $e) {
            $controller->getResponse()->setException($e);
        }
        //Вывод результата
        $controller->getResponse()->appendBody(PHP_EOL)
                                  ->sendResponse();
    }
    
}