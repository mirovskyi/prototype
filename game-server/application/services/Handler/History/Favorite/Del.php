<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 07.06.12
 * Time: 18:33
 * To change this template use File | Settings | File Templates.
 */
class App_Service_Handler_History_Favorite_Del extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @throws Core_Exception
     * @return string
     */
    public function handle()
    {
        //Получаем данные пользователя соц. сети
        $service = $this->getRequest()->get('service');
        $socialUser = new Core_Social_User($service, $this->getRequest()->get('vars'));

        //Проверка наличия у пользователя купленной услуги истории игр
        if (!Core_Game_History::getInstance()->isAllowHistory($socialUser->getId(), $service)) {
            throw new Core_Exception('The service of game history is not available', 3050, Core_Exception::USER);
        }

        //Идентификаторр сессии игры
        $gameid = $this->getRequest()->get('gameid');
        //Удаление записи из избранного
        $result = Core_Game_History::getInstance()->deleteFavoriteHistory(
            $socialUser->getId(),
            $socialUser->getNetwork(),
            $gameid
        );
        //Проверка результата записи
        if (!$result) {
            throw new Core_Exception('Ann error occured while delete history record', 3053);
        }

        //Возвращаем ответ сервера
        return $this->view->render();
    }
}
