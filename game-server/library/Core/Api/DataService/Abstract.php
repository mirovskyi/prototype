<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 17.02.12
 * Time: 15:09
 *
 * Адстрация доступа к API сервера данных игрового сервиса
 */
class Core_Api_DataService_Abstract
{

    /**
     * Настройки
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Объект клиента (JSON-RPC протокол)
     *
     * @var Core_Json_Client
     */
    protected $_client;


    /**
     * __construct
     */
    public function __construct()
    {
        //Получение конфигов API
        $config = Core_Server::getInstance()->getOption('api');
        //Проверка наличия конфигов API сервера данных игрового сервиса
        if (isset($config['dataservice'])) {
            //Установка настроек
            $this->setOptions($config['dataservice']);
        }
    }

    /**
     * Установка настроек
     *
     * @param array $options
     * @return Core_Api_DataService_Abstract
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
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
     * Установка значения параметра в настроеках
     *
     * @param string $name
     * @param mixed $value
     * @return Core_Api_DataService_Abstract
     */
    public function setOption($name, $value)
    {
        $this->_options[$name] = $value;
        return $this;
    }

    /**
     * Получение значения параметра настроек
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        if (isset($this->_options[$name])) {
            return $this->_options[$name];
        } else {
            return $default;
        }
    }

    /**
     * Получение объекта JSON-RPC клиента
     *
     * @return Core_Json_Client
     */
    protected function _getCLient()
    {
        if (null === $this->_client) {
            //Создание JSON-RPC клиента
            $this->_client = new Core_Json_Client(
                $this->getOption('url'),
                $this->getOption('namespace', ''),
                $this->getOption('debug', false)
            );
            //Проверка наличия настроек логов для клиента
            if ($this->getOption('log')) {
                //Создание объекта логов
                $log = new Core_Log($this->getOption('log'));
                $this->_client->setLog($log);
            }
        }

        return $this->_client;
    }

}
