<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.09.12
 * Time: 16:01
 *
 * Обработчик запроса аутентификации пользователя
 */
class App_Service_Handler_User_Auth extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    public function handle()
    {
        //Получение данных пользователя для аутентификации
        $login = $this->getRequest()->get('login');
        $password = $this->getRequest()->get('passwd');

        //Получаем имя платформы (вне соц. сети)
        $nameService = Core_Social_Config::get('htmlServiceName');

        //API для работы с пользователями
        $api = new Core_Api_DataService_User();
        $result = $api->auth($nameService, $login, $password);

        //Передача данных пользователя в шаблон ответа сервера
        $this->view->assign($result);
        //Отдаем ответ сервера
        return $this->view->render();
    }
}
