<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 24.04.12
 * Time: 19:08
 *
 * Контроллер игры Дурак Переводной
 */
class App_DurakTransfer extends Core_Protocol_Server_Handler
{

    public function ping()
    {
        //Обработка запроса
        $service = new App_Service_Handler_DurakTransfer_Ping($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function beatoff()
    {
        //Обработка запроса
        $service = new App_Service_Handler_DurakTransfer_Beatoff($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function throwin()
    {
        //Обработка запроса
        $service = new App_Service_Handler_DurakTransfer_Throw($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function take()
    {
        //Обработка запроса
        $service = new App_Service_Handler_DurakTransfer_Take($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function refuse()
    {
        //Обработка запроса
        $service = new App_Service_Handler_DurakTransfer_Refuse($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

}