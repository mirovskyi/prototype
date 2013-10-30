<?php

/**
 * 
 *
 * @author aleksey
 */
class Core_Server 
{

    /**
     * Экемпляр класса
     *
     * @var Core_Server
     */
    protected static $_instance;
 
    /**
     * Имя окружения
     *
     * @var string 
     */
    protected $_environment;
    
    /**
     * Объект автозагрузки
     *
     * @var Zend_Loader_Autoloader
     */
    protected $_autoloader;
    
    /**
     * Настройки приложения
     *
     * @var array
     */
    protected $_options;
    
    /**
     * Список ключей параметров настроек
     *
     * @var array 
     */
    protected $_optionKeys = array();
    
    /**
     * Объект загрузчика
     *
     * @var Core_Server_Bootstrap 
     */
    protected $_bootstrap;
    
    /**
     * Объект обработчика запросов
     *
     * @var Zend_Json_Server 
     */
    protected $_server;


    /**
     * Получение экземпляра класса сервера
     *
     * @static
     * @return Core_Server
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof Core_Server) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    
    /**
     * __construct
     */
    protected function __construct()
    {
        require_once 'Zend/Loader/Autoloader.php';
        $this->_autoloader = Zend_Loader_Autoloader::getInstance();
    }

    /**
     * Инициализация сервера
     *
     * @param string $environment Окружение
     * @param string $options Файл конфигурации сервера
     * @return Core_Server
     */
    public function init($environment, $options = null)
    {
        $this->_environment = (string) $environment;

        if (null !== $options) {
            if (is_string($options)) {
                $options = $this->_loadConfig($options);
            } elseif ($options instanceof Zend_Config) {
                $options = $options->toArray();
            } elseif (!is_array($options)) {
                throw new Core_Server_Exception('Invalid options provided; must be location of config file, a config object, or an array');
            }

            $this->setOptions($options);
        }
    }
    
    /**
     * Получение имени окружения
     *
     * @return string 
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }
    
    /**
     * Получение объекта автозагрузки
     *
     * @return Zend_Loader_Autoloader 
     */
    public function getAutoloader()
    {
        return $this->_autoloader;
    }
    
    /**
     * Установка настроек
     *
     * @param array $options
     * @return Core_Server 
     */
    public function setOptions(array $options)
    {
        if (!empty($options['config'])) {
            if (is_array($options['config'])) {
                $_options = array();
                foreach ($options['config'] as $tmp) {
                    $_options = $this->mergeOptions($_options, $this->_loadConfig($tmp));
                }
                $options = $this->mergeOptions($_options, $options);
            } else {
                $options = $this->mergeOptions($this->_loadConfig($options['config']), $options);
            }
        }

        $this->_options = $options;

        $options = array_change_key_case($options, CASE_LOWER);

        $this->_optionKeys = array_keys($options);

        if (!empty($options['phpsettings'])) {
            $this->setPhpSettings($options['phpsettings']);
        }

        if (!empty($options['includepaths'])) {
            $this->setIncludePaths($options['includepaths']);
        }

        if (!empty($options['autoloadernamespaces'])) {
            $this->setAutoloaderNamespaces($options['autoloadernamespaces']);
        }
        
        if (!empty($options['appnamespace'])) {
            $this->setServerNamespace($options['appnamespace']);
        }

        return $this;
    }
    
    /**
     * Получение настроек
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * Проверка наличичя параметра настроек
     *
     * @param  string $key
     * @return bool
     */
    public function hasOption($key)
    {
        return in_array(strtolower($key), $this->_optionKeys);
    }

    /**
     * Получение параметра настроек
     *
     * @param  string $key
     * @return mixed
     */
    public function getOption($key)
    {
        if ($this->hasOption($key)) {
            $options = $this->getOptions();
            $options = array_change_key_case($options, CASE_LOWER);
            return $options[strtolower($key)];
        }
        return null;
    }

    /**
     * Рекурсивное слияние массивов
     *
     * @param  array $array1
     * @param  mixed $array2
     * @return array
     */
    public function mergeOptions(array $array1, $array2 = null)
    {
        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {
                if (is_array($array2[$key])) {
                    $array1[$key] = (array_key_exists($key, $array1) && is_array($array1[$key]))
                                  ? $this->mergeOptions($array1[$key], $array2[$key])
                                  : $array2[$key];
                } else {
                    $array1[$key] = $val;
                }
            }
        }
        return $array1;
    }

    /**
     * Установка конфигурации PHP
     *
     * @param  array $settings
     * @param  string $prefix Key prefix to prepend to array values (used to map . separated INI values)
     * @return Zend_Application
     */
    public function setPhpSettings(array $settings, $prefix = '')
    {
        foreach ($settings as $key => $value) {
            $key = empty($prefix) ? $key : $prefix . $key;
            if (is_scalar($value)) {
                ini_set($key, $value);
            } elseif (is_array($value)) {
                $this->setPhpSettings($value, $key . '.');
            }
        }

        return $this;
    }

    /**
     * Установка подключаемых директорий
     *
     * @param  array $paths
     * @return Zend_Application
     */
    public function setIncludePaths(array $paths)
    {
        $path = implode(PATH_SEPARATOR, $paths);
        set_include_path($path . PATH_SEPARATOR . get_include_path());
        return $this;
    }

    /**
     * Добавление пространства имент в автозагрузку
     *
     * @param  array $namespaces
     * @return Zend_Application
     */
    public function setAutoloaderNamespaces(array $namespaces)
    {
        $autoloader = $this->getAutoloader();

        foreach ($namespaces as $namespace) {
            $autoloader->registerNamespace($namespace);
        }

        return $this;
    }
    
    /**
     * Установка пространства имени приложения и правила автозагрузки
     *
     * @param array $options 
     */
    public function setServerNamespace(array $options)
    {
        $resourceLoader = new Zend_Loader_Autoloader_Resource($options);
    }
    
    /**
     * Получение объекта обработчика запросов
     *
     * @return Zend_Json_Server
     */
    public function getServer()
    {
        if ($this->_server == null) {
            $server = new Zend_Json_Server();
            //Проверка наличия настроек сервера
            if ($this->hasOption('server')) {
                $options = $this->getOption('server');
                //Проверка наличия настроек кэширования сервера
                if (isset($options['cache']) && $options['cache'] !== false) {
                    $cache = $options['cache'];
                    if (!Zend_Json_Server_Cache::get($options['cache'], $server)) {
                        $this->_registerServerClasses($server, $options['namespaces']);
                        Zend_Json_Server_Cache::save($options['cache'], $server);
                    }
                } else {
                    $this->_registerServerClasses($server, $options['namespaces']);
                }
            }
            $this->_server = $server;
        }
        return $this->_server;
    }
    
    /**
     * Получение загрузчика
     *
     * @return Core_Server_Bootstrap
     */
    public function getBootstrap()
    {
        if ($this->_bootstrap == null) {
            $this->_bootstrap = new Core_Server_Bootstrap($this->getOption('resource'));
        }
        return $this->_bootstrap;
    }
    
    /**
     * Запуск сервера
     */
    public function run()
    {
        //Создание объекта сервера
        $server = $this->getServer();
        //Загрузка ресурсов приложения
        try {
            $this->getBootstrap()->bootstrap();
        } catch (Exception $e) {
            $server->fault($e->getMessage(), $e->getCode(), $e);
        }

        //Обработка запроса к серверу
        echo $server->handle();

        //Проверка наличия ошибки при обработке
        if ($server->getResponse()->isError()) {
            //Проверка наличия зарегистрированного обработчика исключений
            if ($this->getBootstrap()->hasResource('log')) {
                $log = $this->getBootstrap()->getResource('log')->bootstrap();
                if (!$log->isRegisteredExceptionHandler()) {
                    //Обработчик исключений не зарегистрирован, ничего не делаем
                    return;
                }
            }
            //Выбрасываем исключение (в настройках логирования указывается отображение исключений)
            if ($server->getResponse()->getError()->getData() instanceof Exception) {
                throw $server->getResponse()->getError()->getData();
            }
        }
    }
    
    /**
     * Загрузка конфигов
     *
     * @param string $file
     * @return array 
     */
    protected function _loadConfig($file)
    {
        $environment = $this->getEnvironment();
        $suffix      = pathinfo($file, PATHINFO_EXTENSION);
        $suffix      = ($suffix === 'dist')
                     ? pathinfo(basename($file, ".$suffix"), PATHINFO_EXTENSION)
                     : $suffix;

        switch (strtolower($suffix)) {
            case 'ini':
                $config = new Zend_Config_Ini($file, $environment);
                break;

            case 'xml':
                $config = new Zend_Config_Xml($file, $environment);
                break;

            case 'json':
                $config = new Zend_Config_Json($file, $environment);
                break;

            case 'yaml':
            case 'yml': {
                require_once 'Symfony/yaml/sfYaml.php';
                $config = new Zend_Config_Yaml($file, $environment, array(
                    'yaml_decoder' => array('sfYaml', 'load')
                ));
            }
                break;

            case 'php':
            case 'inc':
                $config = include $file;
                if (!is_array($config)) {
                    throw new Core_Server_Exception('Invalid configuration file provided; PHP file does not return array value');
                }
                if (isset($config[$environment])) {
                    $_config = $config[$environment];
                    if (isset($_config['_extend']) 
                            && isset($config[$_config['_extend']])) {
                        return $this->mergeOptions($config[$_config['_extend']], $_config);
                    }
                    return $_config;
                }
                return $config;
                break;

            default:
                throw new Core_Server_Exception('Invalid configuration file provided; unknown config type');
        }

        return $config->toArray();
    }
    
    /**
     * Регистрация обработчиков сервера
     *
     * @param Zend_Json_Server $server
     * @param array $options 
     */
    protected function _registerServerClasses(Zend_Json_Server $server, array $options)
    {
        if (is_array($options) && count($options)) {
            foreach($options as $namespace => $classes) {
                if (is_string($classes)) {
                    $server->setClass($classes);
                } elseif (is_array($classes)) {
                    foreach($classes as $class) {
                        if (is_string($class)) {
                            $server->setClass($class, $namespace);
                        }
                    }
                }
            }
        }
    }
    
}
