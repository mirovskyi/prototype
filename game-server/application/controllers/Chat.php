<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 14.03.12
 * Time: 16:09
 *
 * Контроллер чата
 */
class App_Chat extends Core_Protocol_Server_Handler
{

    public function message()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Chat_Message($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

}
