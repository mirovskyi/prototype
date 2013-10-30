<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.04.12
 * Time: 17:22
 *
 * Обработчик установки флага пользователю
 */
class App_Service_Handler_Flags_Set extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    public function handle()
    {
        //Получение имени сервиса социальной сети
        $service = $this->getRequest()->get('service');
        //Получение объекта пользователя соц. сети
        $socialUser = new Core_Social_User($service, $this->getRequest()->get('vars'));

        //API для работы с персистентными данными пользователей
        $api = new Core_Api_DataService_Info();
        //Установка флага пользователю
        $flagId = $this->getRequest()->get('id');
        $flagValue = $this->getRequest()->get('value');
        $result = $api->setUserFlag($socialUser->getId(), $service, $flagId, $flagValue);

        //Передача результата в шаблон
        $this->view->assign('result', $result);
        //Отдаем ответ сервера
        return $this->view->render();
    }

}
