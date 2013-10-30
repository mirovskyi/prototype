<?php

class Core_Protocol_Request_Http extends Core_Protocol_Request
{
    
    /**
     * Заголовки запроса
     *
     * @var array
     */
    protected $_headers;

    /**
     * Необработанные данные запроса
     *
     * @var string
     */
    protected $_data;

    /**
     * Класс загрузчика данных запроса
     *
     * @var string
     */
    protected $_loaderClass = 'Core_Protocol_Request_Loader_SimpleXml';

    /**
     * Объект загрузчика данных запроса
     *
     * @var Core_Protocol_Request_Loader_Abstract
     */
    protected $_loader;

    /**
     * Создание нового запроса
     *
     * @param string|Core_Protocol_Request_Loader_Abstract|null $loader
     * @return Core_Protocol_Request_Http
     *
     */
    public function __construct($loader = null)
    {
        $data = @file_get_contents('php://input');
        if (!$data) {
            $this->_fault = new Core_Protocol_Fault(630);
            return;
        }

        $this->_data = $data;

        if (null !== $loader) {
            $this->setLoader($loader);
        }

        $this->load($data);
    }

    /**
     * Метод установка класса/объекта загрузчика данных запроса
     *
     * @param string|Core_Protocol_Request_Loader_Abstract $loader
     * @return Core_Protocol_Request_Http
     */
    public function setLoader($loader)
    {
        if (is_string($loader)) {
            if (!class_exists($loader)) {
                $this->_fault = new Core_Protocol_Fault(630);
                return;
            }
            $this->_loader = new $loader($this->_data);
        } else {
            if ($loader instanceof Core_Protocol_Request_Loader_Abstract) {
                $this->_loader = $loader;
            } else {
                $this->_fault = new Core_Protocol_Fault(630);
                return;
            }
        }
        return $this;
    }

    /**
     * Метод получения объекта загрузчика данных запроса
     *
     * @return Core_Protocol_Request_Loader_Abstract
     */
    public function getLoader()
    {
        if (null === $this->_loader) {
            $this->setLoader($this->_loaderClass);
        }
        return $this->_loader;
    }

    /**
     * Получение необработанных данных запроса
     *
     * @return string
     */
    public function getRawRequest()
    {
        return $this->_data;
    }

    /**
     * Получение заголовков запроса
     *
     * @return array
     */
    public function getHeaders()
    {
        if (null === $this->_headers) {
            $this->_headers = array();
            foreach ($_SERVER as $key => $value) {
                if ('HTTP_' == substr($key, 0, 5)) {
                    $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $this->_headers[$header] = $value;
                }
            }
        }

        return $this->_headers;
    }

    /**
     * Получение полного HTTP запроса, включая заголовки и контент
     *
     * @return string
     */
    public function getFullRequest()
    {
        $request = '';
        foreach ($this->getHeaders() as $key => $value) {
            $request .= $key . ': ' . $value . "\n";
        }

        $request .= $this->_data;

        return $request;
    }

    /**
     * Обработка данных запроса
     *
     * @return void
     */
    public function load()
    {
        //Обработка данных запроса
        $this->getLoader()->setData($this->_data)
                          ->load();

        //Прверка наличия ошибки
        if ($this->getLoader()->isFault()) {
            $this->_fault = $this->getLoader()->getFault();
        } else {
            //Установка данных запроса
            $this->setHandlerName($this->getLoader()->getNamespace())
                 ->setMethod($this->getLoader()->getMethod())
                 ->setParams($this->getLoader()->getParams());
        }
    }

    /**
     * Получение объекта запроса в виде строки
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFullRequest();
    }

}