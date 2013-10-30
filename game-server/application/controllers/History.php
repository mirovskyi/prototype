<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.05.12
 * Time: 14:57
 *
 * Контроллер истории игр
 */
class App_History extends Core_Protocol_Server_Handler
{

    public function games()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_History_Games($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function gamerecords()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_History_GameRecords($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function gamehistory()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_History_GameHistory($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function addfavorite()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_History_Favorite_Add($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function delfavorite()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_History_Favorite_Del($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

}
