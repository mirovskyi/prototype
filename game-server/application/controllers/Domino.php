<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.06.12
 * Time: 11:42
 *
 * Контроллер игры Домино
 */
class App_Domino extends Core_Protocol_Server_Handler
{

    public function ping()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Domino_Ping($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function throwin()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Domino_Throw($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }
}
