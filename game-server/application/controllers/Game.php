<?php

/**
 * Description of Game
 *
 * @author aleksey
 */
class App_Game extends Core_Protocol_Server_Handler
{

    public function create()
    {
        //Создание игры
        $model = new App_Service_Handler_Game_Create($this->getRequest());
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function join()
    {
        //Добавление пользователя в игру
        $model = new App_Service_Handler_Game_Join($this->getRequest());
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function invite()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Game_Invite($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function open()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Game_Open($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }

    public function restart()
    {
        //Обработка запроса
        $service = new App_Service_Handler_Game_Restart($this->getRequest());
        $this->getResponse()->setReturnValue($service->handle());
    }
}