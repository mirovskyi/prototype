<?php

 
class Core_Cli
{

    /**
     * Объект запроса
     *
     * @var Core_Cli_Request
     */
    protected $_request;

    /**
     * Пространство имен для окружения CLI
     *
     * @var string
     */
    protected $_namespace;

    /**
     * Путь к файлам контроллеров
     *
     * @var string
     */
    protected $_controllerDirectory;


    /**
     * __construct
     *
     * @param string $environment
     * @param string|array|null $options
     */
    public function __construct($environment, $options = null)
    {
        require_once 'Core/Server.php';

        $server = Core_Server::getInstance()->init($environment, $options);
        $options = $server->getOptions();
        if (is_array($options) && isset($options['cli'])) {
            $this->setOptions($options['cli']);
        }
    }

    /**
     * Установка параметров
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        if (isset($options['rules'])) {
            $this->setRequest(new Core_Cli_Request($options['rules']));
        }

        if (isset($options['renderExceptions'])) {
            Core_Cli_Response::setRenderExceptions($options['renderExceptions']);
        }

        if (isset($options['appnamespace'])) {
            $resourceLoader = new Zend_Loader_Autoloader_Resource($options['appnamespace']);
            if (isset($options['appnamespace']['namespace'])) {
                $this->setNamespace($options['appnamespace']['namespace']);
            }
        }

        if (isset($options['controllerDirectory'])) {
            $this->setControllerDirectory($options['controllerDirectory']);
        }
    }

    /**
     * Установка объекта запроса
     *
     * @param Core_Cli_Request $request
     * @return void
     */
    public function setRequest(Core_Cli_Request $request)
    {
        $this->_request = $request;
    }

    /**
     * Получение объекта запроса
     *
     * @return Core_Cli_Request
     */
    public function getRequest()
    {
        if (null === $this->_request) {
            $this->setRequest(new Core_Cli_Request());
        }

        return $this->_request;
    }

    /**
     * Установка пространства имен
     *
     * @param string $namespace
     * @return void
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
    }

    /**
     * Получение пространства имен
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Установка пути к файлам контроллеров
     *
     * @param string $directory
     * @return void
     */
    public function setControllerDirectory($directory)
    {
        $this->_controllerDirectory = $directory;
    }

    /**
     * Получение пути к файлам контроллеров
     *
     * @return string
     */
    public function getControllerDirectory()
    {
        return $this->_controllerDirectory;
    }

    /**
     * Запуск приложения
     *
     * @throws Core_Cli_Exception
     * @return void
     */
    public function run()
    {
        //Создание объекта ответа
        $response = new Core_Cli_Response();

        try {
            //Загрузка ресурсов
            Core_Server::getInstance()->getBootstrap()->bootstrap();

            //Создание объекта контроллера
            $className = $this->_getControllerClass();
            $controller = new $className($this->getRequest(), $response);

            //Имя действия
            $action = $this->getRequest()->getActionName() . 'Action';
            if (!method_exists($controller, $action)) {
                throw new Core_Cli_Exception('Call not exists action \'' . $className . '::' . $action . '\'');
            }

            //Вызов действия
            $controller->$action();

            //Вывод ответа
            $controller->getResponse()->render();

        } catch (Core_Exception $e) {
            $response->setException($e)
                     ->render();
        }
    }

    /**
     * Получение имени класса контроллера
     *
     * @throws Core_Cli_Exception
     * @return string
     */
    protected function _getControllerClass()
    {
        $filename = $this->_controllerFilename();
        if (!file_exists($filename)) {
            throw new Core_Cli_Exception('Controller class file \'' . $filename . '\' does not exists');
        }

        require_once $filename;

        $className = $this->_controllerClassName();
        if (!class_exists($className)) {
            throw new Core_Cli_Exception('Undefined controller class \'' . $className . '\'');
        }
        
        return $className;
    }

    /**
     * Получение пути к файлу контроллера
     *
     * @return string
     */
    private function _controllerFilename()
    {
        $directory = $this->getControllerDirectory();
        $controller = ucfirst($this->getRequest()->getControllerName());

        return $directory . '/' . $controller . 'Controller.php';
    }

    /**
     * Получение имени класса контроллера
     *
     * @return string
     */
    private function _controllerClassName()
    {
        if (null !== $this->getNamespace()) {
            $namespace = $this->getNamespace() . '_';
        } else {
            $namespace = '';
        }

        $controllerName = ucfirst($this->getRequest()->getControllerName());
        $controller = $namespace . $controllerName . 'Controller';
        return $controller;
    }

}
