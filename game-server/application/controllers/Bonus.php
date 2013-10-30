<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.09.12
 * Time: 19:04
 *
 * Контроллер обработки запросов начисления бонусов
 */
class App_Bonus extends Core_Protocol_Server_Handler
{

    public function hour()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Bonus_Hour($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

    public function daily()
    {
        //Получение модели обработчика
        $model = new App_Service_Handler_Bonus_Daily($this->getRequest());
        //Обработка запроса
        $this->getResponse()->setReturnValue($model->handle());
    }

}
