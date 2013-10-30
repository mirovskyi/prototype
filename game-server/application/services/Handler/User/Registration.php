<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.09.12
 * Time: 13:16
 *
 * Обработчик регистрации пользователя
 */
class App_Service_Handler_User_Registration extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    public function handle()
    {
        //Получение регистрационных данных пользователя
        $userData = $this->getRequest()->get('data');

        //Получение имени платформы
        $nameService = Core_Social_Config::get('htmlServiceName');

        //Регистрация пользователя
        $api = new Core_Api_DataService_User();
        $result = $api->registration($nameService, $userData);

        //Передача идентификатора сессии и данных игрока
        $this->view->assign($result);
        //Отдаем ответ сервера
        return $this->view->render();
    }

}
