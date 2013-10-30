<?php

 
class Core_Cli_Request
{

    /**
     * Имя контроллера
     *
     * @var string
     */
    protected $_controller;

    /**
     * Имя действия
     *
     * @var string
     */
    protected $_action;

    /**
     * Массив параметров запроса
     *
     * @var array
     */
    protected $_params;


    /**
     * Создание объекта запроса
     *
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        $route = trim($_SERVER['argv'][1]);
        $request = explode(':', $_SERVER['argv'][1]);
        if (isset($request[0])) {
            $this->setControllerName($request[0]);
        }
        if(isset($request[1])) {
            $this->setActionName($request[1]);
        }

        if (is_array($options) && isset($options[$route])) {
            $options = new Zend_Console_Getopt($options[$route]);
            $options->parse();
            $this->setParams($options->getOptions());
        }
    }

    /**
     * Установка имени контроллера
     *
     * @param string $controllerName
     * @return Core_Cli_Request
     */
    public function setControllerName($controllerName)
    {
        $this->_controller = $controllerName;
        return $this;
    }

    /**
     * Получение имени контроллера
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->_controller;
    }

    /**
     * Установка имени действия
     *
     * @param string $actionName
     * @return Core_Cli_Request
     */
    public function setActionName($actionName)
    {
        $this->_action = $actionName;
        return $this;
    }

    /**
     * Получение имени действия
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->_action;
    }

    /**
     * Установка параметров запроса
     *
     * @param array $params
     * @return Core_Cli_Request
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * Получение параметров запроса
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Установка значения параметра запроса
     *
     * @param string $name
     * @param mixed $value
     * @return Core_Cli_Request
     */
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
        return $this;
    }

    /**
     * Получение значения параметра запроса
     *
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        } else {
            return $default;
        }
    }

    /**
     * Проверка наличия параметра запроса
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->_params[$name]);
    }


}
