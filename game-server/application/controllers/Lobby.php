<?php

 
class App_Lobby extends Core_Protocol_Server_Handler
{

    public function open()
    {
        $handler = new App_Service_Handler_Lobby_Open($this->getRequest());
        $this->getResponse()->setReturnValue($handler->handle());
    }

    public function enter()
    {
        $handler = new App_Service_Handler_Lobby_Enter($this->getRequest());
        $this->getResponse()->setReturnValue($handler->handle());
    }

    public function gift()
    {
        $handler = new App_Service_Handler_Lobby_Gift($this->getRequest());
        $this->getResponse()->setReturnValue($handler->handle());
    }

}
