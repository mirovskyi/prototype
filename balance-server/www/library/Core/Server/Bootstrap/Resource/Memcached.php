<?php

/**
 * Description of Memcached
 *
 * @author aleksey
 */
class Core_Server_Bootstrap_Resource_Memcached 
    extends Core_Server_Bootstrap_ResourceAbstract 
{
    
    /**
     * Default Values
     */
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT =  11211;
    const DEFAULT_PERSISTENT = true;
    const DEFAULT_WEIGHT  = 1;
    const DEFAULT_TIMEOUT = 1;
    const DEFAULT_RETRY_INTERVAL = 15;
    
    /**
     * Объект Memcached
     *
     * @var Memcached 
     */
    protected $_memcached;
    
    
    
    /**
     * Загрузка ресурса
     *
     * @return Memcached 
     */
    public function bootstrap()
    {
        return $this->_getMemcached();
    }
    
    /**
     * Создание объекта Memcached
     *
     * @return Memcached 
     */
    protected function _getMemcached()
    {
        if (null !== $this->_memcached) {
            return $this->_memcached;
        }
        if (!$this->hasOption('servers')) {
            return $this->_memcached;
        }
        
        $this->_memcached = new Memcache;
        
        foreach($this->getOption('servers') as $name => $options) {
            if (!is_array($options)) {
                continue;
            }
            
            if (!isset($options['host'])) {
                $options['host'] = self::DEFAULT_HOST;
            }
            if (!isset($options['port'])) {
                $options['port'] = self::DEFAULT_PORT;
            }
            if (!isset($options['persistent'])) {
                $options['persistent'] = self::DEFAULT_PERSISTENT;
            }
            if (!isset($options['weight'])) {
                $options['weight'] = self::DEFAULT_WEIGHT;
            }
            if (!isset($options['timeout'])) {
                $options['timeout'] = self::DEFAULT_TIMEOUT;
            }
            if (!isset($options['retry_interval'])) {
                $options['retry_interval'] = self::DEFAULT_RETRY_INTERVAL;
            }
            
            $this->_memcached->addserver($options['host'], $options['port'], 
                                         $options['persistent'], $options['weight'], 
                                         $options['timeout'], $options['retry_interval']);
        }
        
        return $this->_memcached;
    }
    
}