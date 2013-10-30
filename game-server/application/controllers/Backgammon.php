<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 27.06.12
 * Time: 15:49
 *
 * Контроллер игры в нарды
 */
class App_Backgammon extends Core_Protocol_Server_Handler
{

    public function ping()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Backgammon_Ping($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function move()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Backgammon_Move($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function throwout()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Backgammon_ThrowOut($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

}
