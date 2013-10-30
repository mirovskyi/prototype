<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 21.05.12
 * Time: 12:27
 *
 * Контроллер API игрового сервиса
 */
class App_Api extends Core_Protocol_Server_Handler
{

    public function online()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Api_GetOnline($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

}
