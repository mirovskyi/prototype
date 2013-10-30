<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.09.12
 * Time: 17:29
 *
 * Обработчик запроса обновления данных игры
 */
class App_Service_Handler_User_Update extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    public function handle()
    {
        //Получение идентификатора сессии пользователя
        $sid = $this->getRequest()->get('usersession');
        //Получение данных пользователя для изменения
        $data = $this->getRequest()->get('data');

        //API изменения данных пользователя
        $api = new Core_Api_DataService_User();
        $result = $api->update($sid, $data);

        //Передача обновленных данных пользователя в шаблон ответа сервера
        $this->view->assign($result);
        //Отдаем ответ сервера
        return $this->view->render();
    }
}
