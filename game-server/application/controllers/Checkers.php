<?php

/**
 * Description of Checkers
 *
 * @author aleksey
 */
class App_Checkers extends Core_Protocol_Server_Handler
{
    
    public function ping()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Checkers_Ping($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }
    
    public function update()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Checkers_Update($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }
    
}