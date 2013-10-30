<?php


abstract class Core_Protocol_Request_Loader_Abstract
{

    /**
     * Директория с правилами обработки запроса
     *
     * @var string
     */
    protected static $_rulesDirectory = null;

    /**
     * Необработанные данные запроса
     *
     * @var string
     */
    protected $_data;

    /**
     * Пространство имен вызываемого метода
     *
     * @var string
     */
    protected $_namespace;

    /**
     * Имя вызываемого метода
     *
     * @var string
     */
    protected $_method;

    /**
     * Параметры запроса
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Пути к классам валидаторов
     *
     * @var array
     */
    protected $_validatorPaths = array();

    /**
     * Объект ошибки
     *
     * @var Core_Protocol_Fault
     */
    protected $_fault;


    /**
     * Установка директории с правилами обработки запроса
     *
     * @param string $path
     */
    public static function setRulesDirectory($path)
    {
        self::$_rulesDirectory = $path;
    }

    /**
     * Получение директории с правилами обработки запроса
     *
     * @return string
     */
    public static function getRulesDirectory()
    {
        return self::$_rulesDirectory;
    }

    /**
     * __construct
     *
     * @param string|null $data
     * @return \Core_Protocol_Request_Loader_Abstract
     *
     */
    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->setData($data);
        }
    }

    /**
     * Устновка данных запроса
     *
     * @param string $data
     * @return Core_Protocol_Request_Loader_Abstract
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Получение данных запроса
     *
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Установка пространства имен вызываемого метода
     *
     * @param string $namespace
     * @return Core_Protocol_Request_Loader_Abstract
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
        return $this;
    }

    /**
     * Получение пространства имен вызываемого метода
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Установка имени вызываемого метода
     *
     * @param string $method
     * @return Core_Protocol_Request_Loader_Abstract
     */
    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    /**
     * Получение имени вызываемого метода
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
     * @param array $params
     * @return Core_Protocol_Request_Loader_Abstract
     */
    public function setParams($params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * Установка значения параметра запроса
     *
     * @param string $name
     * @param mixed $value
     * @return Core_Protocol_Request_Loader_Abstract
     */
    public function addParam($name, $value)
    {
        $this->_params[$name] = $value;
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
     * Получение значения параметра запроса
     *
     * @param string $name
     * @param mixed $default
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
     * Установка путей к директориям с классами валидаторов
     *
     * @param array $paths
     *
     * @return Core_Protocol_Request_Loader_Abstract
     */
    public function setValidatorPaths(array $paths)
    {
        $this->_validatorPaths = $paths;
        return $this;
    }

    /**
     * Добавление пути директории к классам валидаторов
     *
     * @param string $namespace Пространство имен классов валидаторов
     * @param string $path      Путь к директории классов валидаторов
     *
     * @return Core_Protocol_Request_Loader_Abstract
     */
    public function addValidatorPath($namespace, $path)
    {
        $this->_validatorPaths[$namespace] = $path;
        return $this;
    }

    /**
     * Получение путей к директориям классов валидаторов
     *
     * @return array
     */
    public function getValidatorPaths()
    {
        return $this->_validatorPaths;
    }

    /**
     * Установка объекта ошибки
     *
     * @param Core_Protocol_Fault $fault
     * @return Core_Protocol_Request_Loader_Abstract
     */
    public function setFault(Core_Protocol_Fault $fault)
    {
        $this->_fault = $fault;
        return $this;
    }

    /**
     * Получение объекта ошибки
     *
     * @return Core_Protocol_Fault
     */
    public function getFault()
    {
        return $this->_fault;
    }

    /**
     * Проверка наличия ошибки
     *
     * @return bool
     */
    public function isFault()
    {
        if ($this->getFault() instanceof Core_Protocol_Fault) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Обработка данных запроса
     *
     * @abstract
     * @return void
     */
    abstract public function load();

    /**
     * Получение объекта валидатора
     *
     * @param string $name   Имя валидатора
     * @param array  $params Параметры валидатора
     *
     * @return Core_Validate_Interface
     * @throws Core_Protocol_Exception
     */
    protected function _getValidator($name, $params = array())
    {
        //Объект валидатора
        $validator = null;

        //Поиск валидатора в указанных директориях
        foreach($this->getValidatorPaths() as $namespace => $path) {
            //Путь к файлу класса валидатора
            $filename = $path . PATH_SEPARATOR . ucfirst($name) . '.php';
            $filename = str_replace('//', '/', $filename);
            //Проверка наличия файла
            if (file_exists($filename)) {
                //Подключаем файл класса валидатора
                require_once $filename;
                //Формирование имени класса валидатора
                $className = $namespace . '_' . ucfirst($name);
                $className = str_replace('__', '_', $className);
                //Проверка наличия класса валидатора
                if (class_exists($className)) {
                    //Создание объекта валидатора
                    $validator = new $className;
                    break;
                }
            }
        }

        //Если валидатор не был найден в подключенных директориях, создаем стандартный объект валидатора
        if (null === $validator) {
            //Формирование имени класса валидатора
            $className = 'Core_Validate_' . ucfirst($name);
            //Проверка наличия класса валидатора
            if (class_exists($className)) {
                //Создание объекта валидатора
                $validator = new $className;
            }
        }

        //Проверка объекта валидтора
        if ($validator instanceof Core_Validate_Interface) {
            //Установка параметров валидатора
            call_user_func_array(array($validator, 'setOptions'), $params);
            //Возвращаем объект валидатора
            return $validator;
        } else {
            throw new Core_Protocol_Exception('Unknown validator used \'' . $name . '\'', 1000);
        }
    }

}
