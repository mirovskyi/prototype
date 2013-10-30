<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.09.12
 * Time: 11:42
 *
 * Вход в лобби из HTML (не из соц. сети)
 */
class App_Service_Handler_Lobby_Enter extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    public function handle()
    {
        //Если в запросе есть идентификатор сессии пользователя, получаем его данные
        $sessionId = $this->getRequest()->get('usersession');
        if ($sessionId) {
            //Получение данных пользователя
            $api = new Core_Api_DataService_User();
            $data = $api->session($sessionId);
            //Передача данных пользователя в шаблон ответа
            $this->view->assign('user', $data);
        }

        //Получение списка игровых серверов
        $api = new Core_Api_DataService_Info();
        $games = $api->getGames();
        //передача данных в шаблон ответа
        $this->view->assign('games', $games);

        //Отдаем ответ сервера
        $this->view->render();
    }
}
