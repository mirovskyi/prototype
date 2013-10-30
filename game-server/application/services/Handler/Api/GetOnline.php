<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 21.05.12
 * Time: 12:29
 * To change this template use File | Settings | File Templates.
 */
class App_Service_Handler_Api_GetOnline extends App_Service_Handler_Abstract
{

    public function handle()
    {
        //Получение данных игрового зала
        $game = $this->getRequest()->get('game');
        $room = new App_Model_Room($game);
        //Передача в шаблон вида количества игроков
        $this->view->assign('online', count($room->getUsers()));
        //Возвращаем ответ сервера
        return $this->view->render();
    }

}
