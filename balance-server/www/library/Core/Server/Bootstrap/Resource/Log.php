<?php

/**
 * Description of Logs
 *
 * @author aleksey
 */
class Core_Server_Bootstrap_Resource_Log extends Core_Server_Bootstrap_ResourceAbstract
{
    
    /**
     * Объект логирования
     *
     * @var Core_Log 
     */
    protected $_log;
    
    
    /**
     * Переопределение конструктора ресурса для регистрации в реестре
     *
     * @param array $options 
     */
    public function __construct(array $options) 
    {
        parent::__construct($options);
        
        //Создание ресурса
        $log = $this->_getLog();
        //Добавляем ресурс в реестр
        Zend_Registry::set('log', $log);
    }
    
    /**
     * Загрузка ресурса
     *
     * @return Core_Log 
     */
    public function bootstrap()
    {
        return $this->_getLog();
    }
    
    /**
     * Получение объекта логирования
     *
     * @return Core_Log 
     */
    protected function _getLog()
    {
        if ($this->_log == null) {
            $this->_log = new Core_Log($this->getOption('filename'));
            if ($this->getOption('isErrorHandler') === true) {
                $this->_log->registerErrorHandler();
            }
            if ($this->getOption('isExceptionHandler') === true) {
                $this->_log->registerExceptionHandler();
            }
            if ($this->getOption('throwExceptions') === true) {
                $this->_log->setThrowException();
            }
        }
        return $this->_log;
    }
    
}