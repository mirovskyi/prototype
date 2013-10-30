<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 19.03.12
 * Time: 19:18
 *
 * Обработчик соглашения с приглашением за игровой стол
 */
class App_Service_Handler_Invite_Confirm extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @throws Exception
     * @return string
     */
    public function handle()
    {
        //Блокируем данные игры
        $this->getGameSession()->lockAndUpdate();

        //Попытка обработки запроса
        try {
            $this->_handle();
        } catch (Exception $e) {
            //Разблокируем данные игры
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохранение и разблокировка данных игры
        $this->getGameSession()->saveAndUnlock();

        //Возвращаем ответ сервера
        return $this->_getResponse();
    }

    /**
     * Обработка запроса
     *
     * @throws Core_Exception
     */
    protected function _handle()
    {
        //Получаем объект оповщения игрока о приглашении за игровой стол
        $notification = new App_Model_Room_Notification_InviteGame();
        $notification->setUserSid($this->getUserSession()->getSid())
                     ->setGameSid($this->getGameSession()->getSid());
        if (!$notification->findByData() || !$this->getUserSession()->hasNotification($notification)) {
            throw new Core_Exception('User does not exists in the list of invitees to a private gaming table',
                                     206,
                                     Core_Exception::USER);
        }

        //Проверка возмжности сесть за игровой стол (проверяется текущее количество игроков)
        $maxCount = $this->getGameSession()->getData()->getMaxPlayersCount();
        $playersCount = count($this->getGameSession()->getData()->getPlayersContainer());
        if ($maxCount <= $playersCount) {
            throw new Core_Exception('Game session has allready busy', 303, Core_Exception::USER);
        }

        //Добавление пользователя за игровой стол
        $this->_joinUser();

        //Удаление уведомления пользователя о приглашении в игру
        $notification->delete();
        $this->getGameSession()->delInvite($this->getUserSession()->getSid());

        //Добавление уведомления о принятии приглашения для создателя игрового стола
        $confirmNotification = new App_Model_Room_Notification_InviteConfirm();
        $confirmNotification->setUserSid($this->getGameSession()->getCreatorSid())
                            ->setGameSid($this->getGameSession()->getSid())
                            ->setInvitedSid($this->getUserSession()->getSid())
                            ->setConfirm()
                            ->save();

        //Если игра стартовала, создаем уведомления об удалении оповещений о приглошении
        if ($this->getGameSession()->getData()->getStatus() != Core_Game_Abstract::STATUS_WAIT) {
            foreach($this->getGameSession()->getInvites() as $userSid) {
                //TODO: создание оповещения о закрытии окна приглашения
                //Удаляем объект оповещения о приглашении пользователя
                $notification = new App_Model_Room_Notification_InviteGame();
                $notification->setUserSid($userSid)
                             ->setGameSid($this->getGameSession()->getSid());
                $notification->findByData();
                $notification->delete();
                //Удаление ссылки оповещения из сессии игры
                $this->getGameSession()->delInvite($userSid);
            }
        }
    }

    /**
     * Добавление пользователя за игровой стол
     *
     * @return void
     */
    protected function _joinUser()
    {
        //Добаляем пользователя за игровой стол
        $joinUser = App_Service_Room_Game_Join::factory(
            $this->getGameSession()->getName(),
            $this->getGameSession(),
            $this->getUserSession()
        );
        $joinUser->join();

        //Удаляем пользователя из игрового зала
        $room = new App_Model_Room($this->getGameSession()->getName(), false);
        $room->lockAndUpdate();
        $room->delUser($this->getUserSession()->getSid());
        $room->saveAndUnlock();
    }

    /**
     * Получение ответа сервера (данные игры)
     *
     * @return string
     */
    protected function _getResponse()
    {
        //Получаем данные игроков
        $userInfo = App_Model_Session_User::getUsersDataFromPlayersContainer(
            $this->getGameSession()->getData()->getPlayersContainer()
        );

        //Данные чата
        $chat = App_Model_Session_GameChat::chat($this->getGameSession()->getSid())->saveXml(
            $this->getUserSession()->getSid(),
            $this->getRequest()->get('chatId', 0)
        );

        //Передача данных в шаблон вида
        $this->view->assign(array(
            'userSess' => $this->getUserSession()->getSid(),
            'gameSess' => $this->getGameSession()->getSid(),
            'game' => $this->getGameSession()->getData(),
            'userInfo' => $userInfo,
            'chat' => $chat
        ));

        //Указываем путь к шаблону игры
        $template = $this->getGameSession()->getData()->getName() . '/update';

        return $this->view->render($template);
    }
}
