<?php

/**
 * Description of Chess
 *
 * @author aleksey
 */
class App_Chess extends Core_Protocol_Server_Handler
{
    
    public function ping()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Chess_Ping($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }
    
    public function update()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Chess_Update($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }
    
    public function promotion()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Chess_Promotion($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }
    
}