<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.04.12
 * Time: 17:34
 *
 * Контроллер работы с флагами пользователей
 */
class App_Flags extends Core_Protocol_Server_Handler
{

    public function set()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Flags_Set($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

}
