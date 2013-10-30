<?php

/**
 * Description of Session
 *
 * @author aleksey
 */
class Core_Session 
{
    
    /**
     * Сессия пользователя
     */
    const USER_NAMESPACE = 'user';
    
    /**
     * Сессия игры
     */
    const GAME_NAMESPACE = 'game';
    
    /**
     * Экземпляр класса
     * @var Core_Session 
     */
    protected static $_instance;
    
    /**
     * Хранилище данных
     * @var array 
     */
    protected $_storage;
    
    /**
     * Метод получения экземпляра класса
     * @return Core_Session
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * __construct
     */
    protected function __construct()
    {
        $this->_storage = array();
    }
    
    /**
     * Метод получения данных сессии
     * @param string $namespace Пространство имен
     * @return mixed
     */
    public function get($namespace)
    {
        if ($this->has($namespace)) {
            return $this->_storage[$namespace];
        } else {
            return null;
        }
    }
    
    /**
     * Метод проверки наличичя данных сессии
     * @param string $namespace
     * @return type 
     */
    public function has($namespace)
    {
        if (isset($this->_storage[$namespace])) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Метод получения данных сессии
     * @param string $namespace Пространство имен
     * @param mixed $data Данные сессии
     * @return Core_Session 
     */
    public function set($namespace, $data)
    {
        $this->_storage[$namespace] = $data;
        return $this;
    }
    
    /**
     * Удаление протранства имен
     * @param string $namespace 
     */
    public function unsetNamespace($namespace)
    {
        if ($this->has($namespace)) {
            unset($this->_storage[$namespace]);
        }
    }
    
    /**
     * Удаление всех данных сессии
     */
    public function clearAll()
    {
        $this->_storage = array();
    }
    
    /**
     * Генерация идентификатора сессии
     * @param string $key [optional]
     * @return string 
     */
    public static function createSID($key = '')
    {
        $strSID = time() . microtime() . $key;
        return md5($strSID);
    }
    
}