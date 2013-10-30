<?php

/**
 * Description of Storage
 *
 * @author aleksey
 */
class Core_Storage 
{
    
    /**
     * Время жизни блокировки ключа по умолчанию
     */
    const DEFAULT_LOCK_TIMEOUT = 5;
    
    /**
     * Наименование типов хранилища
     */
    const FILE = 'file';
    const MEMCACHED = 'memcached';
    
    /**
     * Тип используемого по умолчанию хранилища
     *
     * @var string
     */
    protected static $_type = self::MEMCACHED;
    
    /**
     * Установка типа хранилища по умолчанию
     *
     * @param string $type 
     */
    public static function setDefaultType($type)
    {
        self::$_type = $type;
    }
    
    /**
     * Получение типа хранилища установленного по умолчанию
     *
     * @return string 
     */
    public static function getDefaultType()
    {
        return self::$_type;
    }
    
    /**
     * Инициализация объекта хранилища
     *
     * @param string $type [Optional] Тип хранилища
     * @param array $options [Optional] Настройки хранилища
     * @return Core_Storage_Implementation_Interface
     */
    public static function factory($type = null, $options = array())
    {
        if (!$type) {
            $type = self::getDefaultType();
        }
        $className = 'Core_Storage_Implementation_' . ucfirst($type);
        if (!class_exists($className)) {
            throw new Core_Exception('Unknown storage type', 1010);
        }
        $storage = new $className();
        $storage->setOptions($options);
        if (!$storage instanceof Core_Storage_Implementation_Interface)
        {
            throw new Core_Exception('Invalid storage type', 1011);
        }
        return $storage;
    }
}
