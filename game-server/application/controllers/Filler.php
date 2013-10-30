<?php

/**
 * Description of Filler
 *
 * @author aleksey
 */
class App_Filler extends Core_Protocol_Server_Handler
{
    
    public function ping()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Filler_Ping($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }
    
    public function update()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Filler_Update($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }
    
}