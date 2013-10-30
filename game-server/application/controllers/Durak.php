<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 24.04.12
 * Time: 19:08
 *
 * Контроллер игры Дурак
 */
class App_Durak extends Core_Protocol_Server_Handler
{

    public function ping()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Durak_Ping($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function beatoff()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Durak_Beatoff($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function throwin()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Durak_Throw($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function take()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Durak_Take($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function refuse()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Durak_Refuse($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

}