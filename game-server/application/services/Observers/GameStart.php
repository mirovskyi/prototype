<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.08.12
 * Time: 16:36
 *
 * Класс наблюдателя начала игры
 */
class App_Service_Observers_GameStart implements SplObserver
{

    /**
     * Обработка изменения состояния игры
     *
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject)
    {
        $this->_handle($subject);
    }

    /**
     * Обработка события начала игры
     *
     * @param Core_Game_Abstract $game
     */
    private function _handle(Core_Game_Abstract $game)
    {
        //Проверка статуса начала игры
        if ($game->getStatus() != Core_Game_Abstract::STATUS_PLAY) {
            return;
        }

        //Получаем объект сессии иры из реестра
        $session = $this->_getGameSession($game->getId());
        if (!$session) {
            return;
        }

        //Если игровой стол приватный, удаляем объект оповещения создателя о действиях оппонентов
        if ($session->getMode() == App_Model_Session_Game::PRIVATE_MODE) {
            //Получение объекта оповещения
            $inviteInfoNotification = new App_Model_Room_Notification_InviteInfo();
            $inviteInfoNotification->setUserSid($session->getCreatorSid());
            $inviteInfoNotification->setGameSid($session->getSid());
            if ($inviteInfoNotification->findByData()) {
                //Удаление оповещения
                $inviteInfoNotification->destroy();
            }
        }

        //Удаление наблюдателя
        $game->detach($this);
    }

    /**
     * Получение объекта сессии игры
     *
     * @param string $sid Идентификатор сессии игры
     *
     * @return App_Model_Session_Game|bool
     */
    private function _getGameSession($sid)
    {
        $session = Core_Session::getInstance()->get(Core_Session::GAME_NAMESPACE);
        if ($session != $sid) {
            $session = new App_Model_Session_Game();
            if (!$session->find($sid)) {
                return false;
            }
        }

        return $session;
    }

}
