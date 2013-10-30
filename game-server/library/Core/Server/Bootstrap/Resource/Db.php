<?php

/**
 * Description of Db
 *
 * @author aleksey
 */
class Core_Server_Bootstrap_Resource_Db extends Core_Server_Bootstrap_ResourceAbstract 
{
    
    /**
     * Объект адаптера БД
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;
    
    
    /**
     * Загрузка ресурса
     *
     * @return Zend_Db_Adapter_Abstract 
     */
    public function bootstrap()
    {
        return $this->getDbAdapter();
    }
    
    /**
     * Получение типа адаптера
     *
     * @return string 
     */
    public function getAdapter()
    {
        return $this->getOption('adapter');
    }
    
    /**
     * Получение параметров адаптера
     *
     * @return array 
     */
    public function getParams()
    {
        return $this->getOption('params', array());
    }
    
    /**
     * Проверка наличия флага адаптера по умолчанию
     *
     * @return bool 
     */
    public function isDefaultTableAdapter()
    {
        return $this->getOption('isDefaultTableAdapter', false);
    }
    
    /**
     * Получение/инициализация объекта адаптера БД
     *
     * @return Zend_Db_Adapter_Abstract 
     */
    public function getDbAdapter()
    {
        if ($this->_db == null) {
            if (null !== ($adapter = $this->getAdapter())) {
                //Создание объекта адаптера
                $db = Zend_Db::factory($adapter, $this->getParams());
                //Проверка необходимости устанавливать кэширование
                if ($this->hasOption('defaultMetadataCache')) {
                    $this->setDefaultMetadataCache(
                            $this->getOption('defaultMetadataCache'));
                }
                //Установка объекта адаптера как адаптера по умолчанию
                if ($this->isDefaultTableAdapter()) {
                    Zend_Db_Table::setDefaultAdapter($db);
                }
                
                $this->_db = $db;
            }
        }
        return $this->_db;
    }
    
    /**
     * Установка кэша метаданных таблиц
     *
     * @param Zend_Cache_Core $cache
     * @return Core_Server_Bootstrap_Resource_Db 
     */
    public function setDefaultMetadataCache($cache)
    {
        $metadataCache = null;
        
        if (is_string($cache)) {
            //Объект загрузчика
            $bootstrap = Core_Server::getInstance()->getBootstrap();
            //Проверка наличия ресурса менеджера кэша
            if ($bootstrap->hasResource('cachemanager')) {
                //Объект менеджера кэша
                $cacheManager = $bootstrap->getResource('cachemanager')->bootstrap();
                if (null !== $cacheManager && $cacheManager->hasCache($cache)) {
                    $metadataCache = $cacheManager->getCache($cache);
                }
            }
        } elseif ($cache instanceof Zend_Cache_Core) {
            $metadataCache = $cache;
        }
        
        //Установка кэша для адаптера по умолчанию
        if ($metadataCache instanceof Zend_Cache_Core) {
            Zend_Db_Table::setDefaultMetadataCache($metadataCache);
        }
        
        return $this;
    }
    
}