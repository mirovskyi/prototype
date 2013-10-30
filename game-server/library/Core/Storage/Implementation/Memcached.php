<?php

/**
 * Description of Memcached
 *
 * @author aleksey
 */
class Core_Storage_Implementation_Memcached implements Core_Storage_Implementation_Interface
{
    
    /**
     * Время жизни записи в кэше
     *
     * @var integer 
     */
    protected $_lifetime = false;
    
    /**
     * Объект кэша
     *
     * @var Memcache
     */
    protected $_memcached;
    
    
    /**
     * __construct
     *
     * @param array $options [Optional]
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }
    
    /**
     * Установка объекта бэкнда кэша
     *
     * @param Memcache $cache
     * @return Core_Storage_Implementation_Memcached
     */
    public function setMemcached(Memcache $cache)
    {
        $this->_memcached = $cache;
        return $this;
    }
    
    /**
     * Получение объекта бэкенда кэша
     *
     * @return Memcache 
     */
    public function getMemcached()
    {
        return $this->_memcached;
    }
    
    /**
     * Установка времени жизни записи кэша
     *
     * @param integer $seconds
     * @return Core_Storage_Implementation_Memcached
     */
    public function setLifeTime($seconds)
    {
        $this->_lifetime = $seconds;
        return $this;
    }
    
    /**
     * Получение времени жизни записи кэша
     *
     * @return integer
     */
    public function getLifeTime()
    {
        return $this->_lifetime;
    }

    /**
     * Получение значение ключа из кэша
     *
     * @param string $key
     * @throws Core_Storage_Exception
     * @return mixed
     */
    public function get($key) 
    {
        if (null == $key) {
            throw new Core_Storage_Exception('Invalid key of the record store');
        }
        return $this->getMemcached()->get($key);
    }

    /**
     * Установка значение ключа
     *
     * @param string $key
     * @param string $value
     * @throws Core_Storage_Exception
     * @return boolean
     */
    public function set($key, $value) 
    {
        if (null == $key) {
            throw new Core_Storage_Exception('Invalid key of the record store');
        }
        //Сохранение записи
        return $this->getMemcached()->set($key, $value, $this->_isCompress($value), $this->getLifeTime());
    }

    /**
     * Добавление записи
     *
     * @param string $key
     * @param mixed  $value
     * @throws Core_Storage_Exception
     * @return boolean
     */
    public function add($key, $value)
    {
        if (null == $key) {
            throw new Core_Storage_Exception('Invalid key of the record store');
        }
        return $this->getMemcached()->add($key, $value, $this->_isCompress($value), $this->getLifeTime());
    }

    /**
     * Удаление ключа из кэша
     *
     * @param string $key
     * @throws Core_Storage_Exception
     * @return boolean
     */
    public function delete($key)
    {
        if (null == $key) {
            throw new Core_Storage_Exception('Invalid key of the record store');
        }
        return $this->getMemcached()->delete($key);
    }

    /**
     * Блокировка ключа
     *
     * @param string $key
     * @param int    $timeout
     *
     * @throws Core_Storage_Exception
     * @return boolean
     */
    public function lock($key, $timeout = Core_Storage::DEFAULT_LOCK_TIMEOUT) 
    {
        $i = 0;
        while (!$this->getMemcached()->add($this->_lockKey($key), posix_getpid(), false, $timeout)) {
            usleep(rand(200,2500));
            if (++$i > 1000) {
                throw new Core_Storage_Exception('Faild to lock storage key  ' . $this->_lockKey($key));
            }
        }
        return true;
    }

    /**
     * Получение ID процесса блокирующего запись
     *
     * @param $key
     * @return string
     */
    public function getLockPid($key)
    {
        return $this->getMemcached()->get($this->_lockKey($key));
    }
    
    /**
     * Разблокировка ключа
     *
     * @param string $key
     * @return boolean 
     */
    public function unlock($key) 
    {
        return $this->getMemcached()->delete($this->_lockKey($key));
    }
    
    /**
     * Проверка блокировки ключа
     *
     * @param string $key
     * @return boolean
     */
    public function isLocked($key)
    {
        return (bool)$this->getMemcached()->get($this->_lockKey($key));
    }

    /**
     * Установка настроек
     *
     * @param array $options
     * @throws Core_Exception
     */
    public function setOptions(array $options) 
    {
        if (isset($options['lifetime'])) {
            $this->setLifeTime($options['lifetime']);
        }
        if (isset($options['memcached']) && 
                $options['memcached'] instanceof Memcache) {
            $this->setMemcached($options['memcached']);
        } else {
            //Проверка наличия ресурса Memcache в загрузчике
            $bootstrap = Core_Server::getInstance()->getBootstrap();
            if (!$bootstrap->hasResource('memcached')) {
                throw new Core_Exception('Undefined memcached resource');
            }
            
            $memcached = $bootstrap->getResource('memcached')->bootstrap();
            
            $this->setMemcached($memcached);
        }
    }

    /**
     * Получение ключа для блокировки записи
     *
     * @param $key
     * @return string
     */
    private function _lockKey($key)
    {
        return 'lock:' . $key;
    }

    /**
     * Определение необходимости сжатия данных
     *
     * @param mixed $value
     * @return bool|int
     */
    private function _isCompress($value)
    {return false;
        //В зависимости от типа данных определяем необходимость сжатия
        return is_bool($value) || is_int($value) || is_float($value) ? false : MEMCACHE_COMPRESSED;
    }
    
}