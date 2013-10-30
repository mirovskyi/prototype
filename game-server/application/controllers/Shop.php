<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 9:59
 *
 * Контроллер магазина
 */
class App_Shop extends Core_Protocol_Server_Handler
{

    public function open()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Shop_Open($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function buy()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Shop_Buy($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

}
