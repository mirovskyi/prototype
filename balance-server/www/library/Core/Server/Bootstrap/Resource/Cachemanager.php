<?php

/**
 * Description of Cachemanager
 *
 * @author aleksey
 */
class Core_Server_Bootstrap_Resource_Cachemanager extends Core_Server_Bootstrap_ResourceAbstract
{
    
    /**
     * Объект менеджера кэша
     *
     * @var Zend_Cache_Manager
     */
    protected $_manager;

    
    /**
     * Загрузка ресурса
     *
     * @return Zend_Cache_Manager 
     */
    public function bootstrap()
    {
        return $this->_getCacheManager();
    }
    
    /**
     * Получение объекта менеджера
     *
     * @return Zend_Cache_Manager 
     */
    protected function _getCacheManager()
    {
        if ($this->_manager == null) {
            $this->_manager = new Zend_Cache_Manager();

            $options = $this->getOptions();
            foreach ($options as $key => $value) {
                if ($this->_manager->hasCacheTemplate($key)) {
                    $this->_manager->setTemplateOptions($key, $value);
                } else {
                    $this->_manager->setCacheTemplate($key, $value);
                }
            }
        }
        return $this->_manager;
    }
    
}