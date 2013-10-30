<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.09.12
 * Time: 14:16
 *
 * Контроллер работы с пользователями
 */
class App_User extends Core_Protocol_Server_Handler
{

    public function registration()
    {
        //Обработчик
        $handler = new App_Service_Handler_User_Registration($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($handler->handle());
    }

    public function update()
    {
        //Обработчик
        $handler = new App_Service_Handler_User_Update($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($handler->handle());
    }

    public function auth()
    {
        //Обработчик
        $handler = new App_Service_Handler_User_Auth($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($handler->handle());
    }

}
