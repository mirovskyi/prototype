<?php


class Core_Protocol_Request
{

    /**
     * Кодировка
     *
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Имя обработчика
     *
     * @var string
     */
    protected $_handlerName = null;

    /**
     * Вызываемый метод
     *
     * @var string
     */
    protected $_method = null;

    /**
     * Параметры запроса
     *
     * @var mixed
     */
    protected $_params = null;

    /**
     * Объект ошибки
     *
     * @var Core_Protocol_Fault
     */
    protected $_fault = null;


    /**
     * Создание нового запроса
     *
     * @param string|null $handler
     * @param string|null $method
     * @param mixed|null $params
     */
    public function __construct($handler = null, $method = null, $params = null)
    {
        if (null !== $handler) {
            $this->setHandlerName($handler);
        }
        if (null !== $method) {
            $this->setMethod($method);
        }
        if (null !== $params) {
            $this->setParams($params);
        }
    }

    /**
     * Magic method __get
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Magic method __isset
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_params[$name]);
    }

    /**
     * Установка кодировки
     *
     * @param string $encoding
     * @return Core_Protocol_Request
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Получение кодировки
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Установка имени обработчика
     *
     * @param string $handler
     * @return Core_Protocol_Request
     */
    public function setHandlerName($handler)
    {
        $this->_handlerName = $handler;
        return $this;
    }

    /**
     * Получение имени обработчика
     *
     * @return string
     */
    public function getHandlerName()
    {
        return $this->_handlerName;
    }

    /**
     * Установка вызываемого метода
     *
     * @param string $method
     * @return Core_Protocol_Request
     */
    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    /**
     * Получение вызываемого метода
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Установка параметров запроса
     *
     * @param mixed $params
     * @return Core_Protocol_Request
     */
    public function setParams($params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * Получение параметров запроса
     *
     * @return mixed
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Получение значения параметра запроса
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        } else {
            return $default;
        }
    }

    /**
     * Установка параметра запроса
     *
     * @param string $name  Имя параметра
     * @param mixed  $value Значение параметра
     */
    public function set($name, $value)
    {
        $this->_params[$name] = $value;
    }

    /**
     * Проверка наличия ошибки
     *
     * @return bool
     */
    public function isFault()
    {
        if ($this->_fault instanceof Core_Protocol_Fault) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получение объекта ошибки
     *
     * @return Core_Protocol_Fault|null
     */
    public function getFault()
    {
        return $this->_fault;
    }

    /**
     * Загрузка данных запроса
     *
     * @return void
     */
    public function load()
    {}

}
