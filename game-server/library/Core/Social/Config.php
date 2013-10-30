<?php

/**
 * Description of Config
 *
 * @author aleksey
 */
class Core_Social_Config
{
    
    /**
     * Экземпляр класса
     *
     * @var Core_Social_Config 
     */
    protected static $_instance;
    
    /**
     * Объект конфига
     *
     * @var Zend_Config
     */
    protected $_config;
    
    
    /**
     * Получение экземпляра класса
     *
     * @return Core_Social_Config 
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof Core_Social_Config) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * Получение значения параметра из конфига
     *
     * @param string $name
     * @param mixed $default 
     * @return Zend_Config
     */
    public static function get($name, $default = null) 
    {
        return self::getInstance()->getConfig()->get($name, $default);
    }
    
    /**
     * Проверка наличия параметра в конфиге
     *
     * @param string $name
     * @return bool 
     */
    public static function has($name)
    {
        if (null !== self::get($name, null)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Установка объекта конфигов
     *
     * @param Zend_Config $config
     * @return Core_Social_Config 
     */
    public function setConfig(Zend_Config $config)
    {
        $this->_config = $config;
        return $this;
    }
    
    /**
     * Получение объекта конфигов
     *
     * @return Zend_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }
    
    /**
     * __construct
     */
    protected function __construct() 
    {
        //Поверка наличия пути к файлу конфигов
        if (Core_Server::getInstance()->hasOption('socialnetwork_config')) {
            //Путь к файлу конфигов
            $file = Core_Server::getInstance()->getOption('socialnetwork_config');
            //Данные конфига
            $config = $this->_loadConfig($file);
        } else {
            $config = array();
        }
        $this->setConfig(new Zend_Config($config));
    }

    /**
     * Загрузка данных конфигов из файла
     *
     * @param string $file
     * @throws Core_Social_Exception
     * @return array
     */
    private function _loadConfig($file)
    {
        $environment = Core_Server::getInstance()->getEnvironment();
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
                    throw new Core_Social_Exception('Invalid configuration file provided; PHP file does not return array value');
                }
//                if (isset($config[$environment])) {
//                    $_config = $config[$environment];
//                    if (isset($_config['_extend']) 
//                            && isset($config[$_config['_extend']])) {
//                        return $this->mergeOptions($config[$_config['_extend']], $_config);
//                    }
//                    return $_config;
//                }
                return $config;
                break;

            default: return array();
        }

        return $config->toArray();
    }
    
}