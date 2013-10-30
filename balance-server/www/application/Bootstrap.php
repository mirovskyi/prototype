<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    /**
     * Загрузка конфиглв сервера
     */
    public function _initServerconfig()
    {
        //Данные конфиглв сервера
        $serverConfig = $this->getOption('serverconfig');
        if (!$serverConfig) {
            return;
        }

        //Получаем путь к конфигам сервера
        $path = $serverConfig['path'];
        //Загрузка объекта конфигов
        $configs = $this->_loadConfig($path, $this->getApplication()->getEnvironment());
        //Запись конфигов сервера в реестр
        Zend_Registry::set('serverconfig', new Zend_Config($configs));
    }

    /**
     * Загрузка конфигов
     *
     * @param string $file
     *
     * @param $environment
     * @throws Zend_Exception
     * @return array
     */
    protected function _loadConfig($file, $environment)
    {
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
                    throw new Zend_Exception('Invalid configuration file provided; PHP file does not return array value');
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
                throw new Zend_Exception('Invalid configuration file provided; unknown config type');
        }

        return $config->toArray();
    }


}
