<?php

/**
 * Description of Room
 *
 * @author aleksey
 */
class App_Room extends Core_Protocol_Server_Handler
{
    
    public function open()
    {
        //Обработчик
        $handler = new App_Service_Handler_Room_Open($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($handler->handle());
    }

    public function ping()
    {
        //Обработчик
        $handler = new App_Service_Handler_Room_Ping($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($handler->handle());
    }

    public function users()
    {
        //Обработчик
        $handler = new App_Service_Handler_Room_Users($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($handler->handle());
    }

    public function quit()
    {
        //Обработчик
        $handler = new App_Service_Handler_Room_Quit($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($handler->handle());
    }

    public function message()
    {
        //Обработчик
        $handler = new App_Service_Handler_Room_Message($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($handler->handle());
    }
    
}
