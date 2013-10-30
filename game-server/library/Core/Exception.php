<?php

/**
 * Description of Exception
 *
 * @author aleksey
 */
class Core_Exception extends Zend_Exception 
{
    
    /**
     * Типы ошибок
     */
    const SYSTEM = 'system';
    const USER = 'user';
    
    /**
     * Тип исключения
     *
     * @var string 
     */
    protected $_type;
    
    
    /**
     * __construct
     *
     * @param string $msg
     * @param integer $code
     * @param string|null $type
     * @param Exception|null $previous 
     */
    public function __construct($msg = '', $code = 0, $type = null, Exception $previous = null) 
    {
        parent::__construct($msg, $code, $previous);
        
        if (null !== $type) {
            $this->setType($type);
        } else {
            $this->setType(Core_Exception::SYSTEM);
        }
    }
    
    /**
     * Установка типа исключения
     *
     * @param string $type
     * @return Core_Exception 
     */
    public function setType($type) 
    {
        $this->_type = $type;
        return $this;
    }
    
    /**
     * Получение типа исключения
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }
    
}