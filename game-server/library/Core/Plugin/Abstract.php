<?php

/**
 * Abstract plugin
 *
 * @author aleksey
 */
class Core_Plugin_Abstract 
{
    
    /**
     * Объект сервера
     *
     * @var Core_Protocol_Server 
     */
    protected $_server;
    
    
    /**
     * __construct
     *
     * @param Core_Protocol_Server|null $server 
     */
    public function __construct(Core_Protocol_Server $server = null)
    {
        if ($server instanceof Core_Protocol_Server) {
            $this->setServer($server);
        }
    }
    
    /**
     * Установка объекта сервера
     *
     * @param Core_Protocol_Server $server
     * @return Core_Plugin_Abstract 
     */
    public function setServer(Core_Protocol_Server $server) 
    {
        $this->_server = $server;
        return $this;
    }
    
    /**
     * Получение объекта сервера
     *
     * @return Core_Protocol_Server 
     */
    public function getServer()
    {
        return $this->_server;
    }
    
    /**
     * pre-handle
     */
    public function preHandle()
    {}
    
    /**
     * post-handle
     */
    public function postHandle()
    {}
    
}