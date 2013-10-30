<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 9:59
 *
 * Контроллер платежа
 */
class App_Payment extends Core_Protocol_Server_Handler
{

    public function success()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Payment_Success($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

}
