<?php

/**
 * Description of Bootstrap
 *
 * @author aleksey
 */
class Core_Server_Bootstrap 
{
    
    /**
     * Настройки ресурсов сервера
     *
     * @var array
     */
    protected $_options = array();
    
    /**
     * Список ресурсов сервера
     *
     * @var array 
     */
    protected $_resources = array();
    
    
    /**
     * __construct
     *
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
    
    /**
     * Установка настроек ресурсов сервера
     *
     * @param array $options
     * @return Core_Server_Bootstrap 
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
        return $this;
    }
    
    /**
     * Получение настроек ресурсов сервера
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * Установка списка ресурсов
     *
     * @param array $resources
     * @return Core_Server_Bootstrap 
     */
    public function setResources(array $resources)
    {
        $this->_resources = $resources;
        return $this;
    }
    
    /**
     * Добавление ресурса
     *
     * @param string $name
     * @param Core_Server_Bootstrap_ResourceAbstract $resource
     * @return Core_Server_Bootstrap 
     */
    public function addResource($name, Core_Server_Bootstrap_ResourceAbstract $resource)
    {
        $this->_resources[$name] = $resource;
        return $this;
    }
    
    /**
     * Получение списка ресурсов
     *
     * @return array 
     */
    public function getResources()
    {
        return $this->_resources;
    }
    
    /**
     * Получение ресурса
     *
     * @param string $name
     * @return Core_Server_Bootstrap_ResourceAbstract 
     */
    public function getResource($name)
    {
        if (isset($this->_resources[$name])) {
            return $this->_resources[$name];
        } else {
            return false;
        }
    }
    
    /**
     * Проверка наличия ресурса
     *
     * @param string $name
     * @return bool 
     */
    public function hasResource($name)
    {
        return isset($this->_resources[$name]);
    }
    
    /**
     * Инициализация и установка параметров ресурсов
     */
    public function bootstrap()
    {
        //Проверка наличия настроек ресурсов
        if (count($this->getOptions() > 0)) {
            //Префис классов ресурсов
            $classPrefix = get_class($this) . '_Resource_';
            //Получаем настройки каждого ресурса
            foreach($this->getOptions() as $resourceName => $options) {
                //Имя класса ресурса
                $className = $classPrefix . ucfirst($resourceName);
                if (class_exists($className)) {
                    //Создание объекта ресурса
                    $resource = new $className($options);
                    //Регистрация ресурса
                    $this->addResource($resourceName, $resource);
                }
            }
        }
    }
    
}