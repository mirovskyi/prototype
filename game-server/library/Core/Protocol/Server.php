<?php


class Core_Protocol_Server
{

    /**
     * Кодировка
     *
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Объект запроса
     *
     * @var Core_Protocol_Request
     */
    protected $_request;

    /**
     * Объект ответа
     *
     * @var Core_Protocol_Response
     */
    protected $_response;

    /**
     * Класс запроса
     *
     * @var string
     */
    protected $_requestClass = 'Core_Protocol_Request_Http';

    /**
     * Класс обработки данных запроса
     *
     * @var string
     */
    protected $_requestLoaderClass = null;

    /**
     * Класс ответа
     *
     * @var string
     */
    protected $_responseClass = 'Core_Protocol_Response_Http';

    /**
     * Директория обработчиков запросов
     *
     * @var string
     */
    protected $_handlerDirectory;


    /**
     * __construct
     *
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Установка параметров сервера
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        //Установка кодировки
        if (isset($options['encoding'])) {
            $this->setEncoding($options['encoding']);
        }

        //Настройки обработки запросов
        if (isset($options['request'])) {
            if (isset($options['request']['class'])) {
                $this->_requestClass = $options['request']['class'];
            }

            if (isset($options['request']['loaderClass'])) {
                $this->_requestLoaderClass = $options['request']['loaderClass'];
            }

            if (isset($options['request']['rulesDirectory'])) {
                Core_Protocol_Request_Loader_Abstract::setRulesDirectory($options['request']['rulesDirectory']);
            }

            //Инициалиация объекта запроса
            $requestClass = $this->_requestClass;
            $requestLoaderClass = $this->_requestLoaderClass;
            $this->setRequest(new $requestClass($requestLoaderClass));
        }

        //Настройки формирования ответов
        if (isset($options['response'])) {
            if (isset($options['response']['class'])) {
                $this->_responseClass = $options['response']['class'];
            }
        }

        //Установка директории обработчиков запросов
        if (isset($options['handlerDirectory'])) {
            $this->_handlerDirectory = $options['handlerDirectory'];
        }

        //Настройка обработки исключений
        if (isset($options['avaliableExceptions'])) {
            Core_Protocol_Server_Fault::attachFaultException($options['avaliableExceptions']);
        }
        if (isset($options['faultObservers'])) {
            $observerClasses = (array)$options['faultObservers'];
            foreach($observerClasses as $class) {
                Core_Protocol_Server_Fault::attachObserver($class);
            }
        }
    }

    /**
     * Установка кодировки
     *
     * @param string $encoding
     * @return Core_Protocol_Server
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
     * Установка объекта запроса
     *
     * @param Core_Protocol_Request $request
     * @return Core_Protocol_Server
     */
    public function setRequest(Core_Protocol_Request $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Получение объекта запроса
     *
     * @return Core_Protocol_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Установка имени класса ответа сервера
     *
     * @param string $className
     * @return Core_Protocol_Server
     */
    public function setResponseClass($className)
    {
        $this->_responseClass = $className;
        return $this;
    }

    /**
     * Получение имени класса ответа сервера
     *
     * @return string
     */
    public function getResponseClass()
    {
        return $this->_responseClass;
    }

    /**
     * Установка объекта ответа
     *
     * @param Core_Protocol_Response|Core_Protocol_Fault $response
     * @return Core_Protocol_Server
     */
    public function setResponse($response)
    {
        if (!$response instanceof Core_Protocol_Response &&
                !$response instanceof Core_Protocol_Fault) {
            throw new Core_Protocol_Server_Exception('Invalid response object');
        }
        $response->setEncoding($this->getEncoding());
        $this->_response = $response;
        return $this;
    }

    /**
     * Получение объкта ответа
     *
     * @return Core_Protocol_Response|Core_Protocol_Fault
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Установка пути к директории обработчиков (контроллеров)
     *
     * @param string $path
     * @return Core_Protocol_Server
     */
    public function setHandlerDirectory($path)
    {
        $this->_handlerDirectory = $path;
        return $this;
    }

    /**
     * Получение пути к директории обработчиков (контроллеров)
     *
     * @return string
     */
    public function getHandlerDirectory()
    {
        return $this->_handlerDirectory;
    }

    /**
     * Создание объекта ошибки
     *
     * @param Exception|null $fault
     * @param int $code
     * @return Core_Protocol_Fault
     */
    public function fault($fault = null, $code = 500)
    {
        if (!$fault instanceof Exception) {
            $fault = (string) $fault;
            if (empty($fault)) {
                $fault = 'Server Internal Error';
            }
            $fault = new Core_Protocol_Server_Exception($fault, $code);
        }

        return Core_Protocol_Server_Fault::getInstance($fault);
    }

    /**
     * Обработка запроса
     *
     * @param Core_Protocol_Request|null $request
     * @return void
     */
    public function handle(Core_Protocol_Request $request = null)
    {
        try {
            //Объект запроса
            if (null !== $request) {
                $this->setRequest($request);
            }
            if (!$this->getRequest() instanceof Core_Protocol_Request) {
                $this->setRequest(new Core_Protocol_Request_Http());
            }

            $this->getRequest()->setEncoding($this->getEncoding());

            //Проверка ошибок в запроса
            if ($this->getRequest()->isFault()) {
                $response = $this->getRequest()->getFault();
            } else {
                //Обработка запроса
                $response = $this->_handle();
            }
        } catch (Exception $e) {
            $response = $this->fault($e);
        }

        //Установка объекта ответа
        $this->setResponse($response);
    }

    /**
     * Запуск обработчика
     *
     * @throws Core_Protocol_Server_Exception
     * @return Core_Protocol_Response
     */
    protected function _handle()
    {
        //Объект ответа
        $responseClass = $this->getResponseClass();
        if (!class_exists($responseClass)) {
            throw new Core_Protocol_Server_Exception('Unknown response class');
        }
        $response = new $responseClass();

        //Пространство имен приложения
        $appNamespace = Core_Server::getInstance()->getOption('appnamespace');
        if ($appNamespace && isset($appNamespace['namespace'])) {
            $namespace = $appNamespace['namespace'] . '_';
        } else {
            $namespace = '';
        }

        //Путь к файлу класса обработчика

        $handlerName = $this->_toCamelCase($this->getRequest()->getHandlerName());
        $handlerPath = $this->getHandlerDirectory() . '/' . $handlerName . '.php';

        //Подключаем файл класса
        require_once $handlerPath;

        //Инициализация объекта обработчика
        $handlerClass = $namespace . $handlerName;
        if (!class_exists($handlerClass)) {
            throw new Core_Protocol_Server_Exception(sprintf('Handler class %s not found in %s', $handlerClass, $handlerPath));
        }
        $handler = new $handlerClass($this->getRequest(), $response);

        //Запуск обработчика
        return $handler->run();
    }

    /**
     * Преобразование строки в CamelCase формат
     *
     * @param string $string
     * @return string
     */
    private function _toCamelCase($string)
    {
        $parts = explode('_', $string);
        $ucParts = array_map('ucfirst', $parts);
        return implode('', $ucParts);
    }

}

