<?php

/**
 * Description of ResourceAbstract
 *
 * @author aleksey
 */
abstract class Core_Server_Bootstrap_ResourceAbstract 
{
    
    /**
     * Настройки ресурса
     *
     * @var array
     */
    protected $_options;
    
    /**
     * __construct
     *
     * @param array $options 
     */
    public function __construct(array $options) 
    {
        $this->setOptions($options);
    }
    
    /**
     * Установка настроек ресурса
     *
     * @param array $options
     * @return Core_Server_Bootstrap_ResourceAbstract 
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
        return $this;
    }
    
    /**
     * Получение настроек ресурса
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * Получение параметра из настроек ресурса
     *
     * @param string $key
     * @param mixed $default
     * @return mixed 
     */
    public function getOption($key, $default = null)
    {
        if ($this->hasOption($key)) {
            return $this->_options[$key];
        } else {
            return $default;
        }
    }
    
    /**
     * Проверка наличия параметра в настройках ресурса
     *
     * @param string $key
     * @return boolean 
     */
    public function hasOption($key)
    {
        return isset($this->_options[$key]);
    }
    
    /**
     * Загрузка ресурса
     */
    abstract public function bootstrap();
    
}