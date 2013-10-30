<?php


class App_Invite extends Core_Protocol_Server_Handler
{

    public function confirm()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Invite_Confirm($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function decline()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Invite_Decline($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

}
