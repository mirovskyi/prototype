<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 08.03.12
 * Time: 11:17
 *
 * Контроллер событий
 */
class App_Event extends Core_Protocol_Server_Handler
{

    public function bet()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Event_Bet($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function draw()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Event_Draw($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function surrender()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Event_Surrender($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

}
