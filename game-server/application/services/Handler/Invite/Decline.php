<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 19.03.12
 * Time: 19:26
 *
 * Обработчик отказа от приглашением за игровой стол
 */
class App_Service_Handler_Invite_Decline extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    public function handle()
    {

        //Попытка обработки запроса
        $this->_handle();

        //Возвращаем ответ сервера
        return $this->view->render();
    }

    /**
     * Обработка запроса
     *
     * @throws Core_Exception
     */
    protected function _handle()
    {
        //Удаляем пользователя из списка приглашенных
        $this->getGameSession()->lockAndUpdate();
        $this->getGameSession()->delInvite($this->getUserSession()->getSid());
        $this->getGameSession()->saveAndUnlock();

        //Удаление оповещения пользователя о приглашении в игру
        $inviteNotification = new App_Model_Room_Notification_InviteGame();
        $inviteNotification->setUserSid($this->getUserSession()->getSid())
            ->setGameSid($this->getGameSession()->getSid());
        if ($inviteNotification->findByData()) {
            $inviteNotification->destroy();
        }

        //Добавляем уведомление создателя игры об отказе пользователя
        $inviteInfoNotification = new App_Model_Room_Notification_InviteInfo();
        $inviteInfoNotification->setUserSid($this->getGameSession()->getCreatorSid());
        $inviteInfoNotification->setGameSid($this->getGameSession()->getSid());
        if ($inviteInfoNotification->findByData()) {
            $inviteInfoNotification->lockAndUpdate();
            //Добавление пользователя в список отказавшихся
            if ($inviteInfoNotification->addDeclineUser($this->getUserSession()->getSid())) {
                //Сбрасываем флаг уведомления создателя игры (для обновления оповещения)
                $inviteInfoNotification->resetNotify();
            }
            $inviteInfoNotification->saveAndUnlock();
        }
    }
}
