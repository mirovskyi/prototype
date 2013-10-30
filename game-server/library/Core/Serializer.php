<?php

/**
 * Description of Serializer
 *
 * @author aleksey
 */
class Core_Serializer 
{
    
    /**
     * Тип сериализации json
     */
    const JSON = 'json';
    
    /**
     * Тип сериализации
     *
     * @var string 
     */
    protected $_type = self::JSON;
    
    /**
     * Экземпляр класса
     *
     * @var Core_Serializer 
     */
    protected static $_instance;
    
    /**
     * Получение экземпляра класса
     *
     * @return Core_Serializer
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Установка типа сериализации по умолчанию
     *
     * @param string $type
     */
    public function setDefaultType($type)
    {
        $this->_type = $type;
    }
    
    /**
     * Получение типа сериализации по умолчанию
     *
     * @return string 
     */
    public function getDefaultType()
    {
        return $this->_type;
    }
    
    /**
     * Сериализация 
     *
     * @param object|array $object
     * @param string $type [optional] Тип сериализации
     * @return string 
     */
    public static function serialize($object, $type = null)
    {
        $engine = self::getInstance()->_getEngine($type);
        
        if (null === $engine) {
            throw new Core_Serializer_Exception('Unknown serialize engine type');
        }
        
        return $engine->encode($object);
    }
    
    /**
     * Получение объекта из сериализованной строки
     *
     * @param string $serialized
     * @param string $type [optional] Тип сериализации
     * @return Core_Serializer_Interface 
     */
    public static function unserialize($serialized, $type = null)
    {
        $engine = self::getInstance()->_getEngine($type);
        
        if (null === $engine) {
            throw new Core_Serializer_Exception('Unknown serialize engine type');
        }
        
        return $engine->decode($serialized);
    }
    
    /**
     * Получение объекта реализующего сериализацию
     *
     * @param string $type [optional] Тип сериализации
     * @return Core_Serializer_EngineInterface
     */
    protected function _getEngine($type = null)
    {
        if (null === $type) {
            $type = $this->getDefaultType();
        }
        
        $engine = null;
        
        $classNamespace = get_class($this) . '_Engine_';
        $className = $classNamespace . ucfirst($type);
        if (class_exists($className)) {
            $engine = new $className;
        }
        
        return $engine;
    }
    
}