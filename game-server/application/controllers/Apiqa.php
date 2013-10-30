<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 10.07.12
 * Time: 13:16
 *
 * Контроллер API для тестов игрового сервиса
 */
class App_Apiqa extends Core_Protocol_Server_Handler
{
    //ДУРАК
    public function durakinfo()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Api_QA_Durak_Info($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function duraksave()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Api_QA_Durak_Save($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

    //ДОМИНО
    public function dominoinfo()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Api_QA_Domino_Info($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function dominosave()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Api_QA_Domino_Save($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }
}
