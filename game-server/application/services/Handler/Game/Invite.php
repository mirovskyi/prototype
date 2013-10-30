<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 20.03.12
 * Time: 9:20
 *
 * Обработка запроса приглашения оппонентов за игровой стол
 */
class App_Service_Handler_Game_Invite extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @throws Exception
     * @return string
     */
    public function handle()
    {
        //Получаем актуальные данные игрового зала и блокируем их
        $room = new App_Model_Room($this->getGameSession()->getName());

        //Блокируем и получаем актуальные данные игры
        $this->getGameSession()->lockAndUpdate();

        //Обработка запроса
        try {
            $users = $this->_handle($room);
        } catch (Exception $e) {
            //Разблокируем данные игры
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохраняем и разблокируем данные игры
        $this->getGameSession()->saveAndUnlock();

        //Возвращаем ответ сервера
        $this->view->assign('users', $users);
        return $this->view->render();
    }

    /**
     * Обработка запроса
     *
     * @param App_Model_Room $room
     * @return array
     * @throws Core_Exception
     */
    protected function _handle(App_Model_Room $room)
    {
        //Проверка соответствия создателя игрового стола и текущего игрока
        if ($this->getGameSession()->getCreatorSid() != $this->getUserSession()->getSid()) {
            throw new Core_Exception('Permission denied to edit game settings', 209, Core_Exception::USER);
        }

        //Проверка возможности добавления игроков за игровой стол
        $playersCount = count($this->getGameSession()->getData()->getPlayersContainer());
        if ($this->getGameSession()->getData()->getMaxPlayersCount() <= $playersCount) {
            throw new Core_Exception('Game session has allready busy', 303, Core_Exception::USER);
        }

        //Проверка типа игрового стола
        if ($this->getGameSession()->getMode() != App_Model_Session_Game::PRIVATE_MODE) {
            //Приглашать оппонентов можно только за приватный игровой стол
            throw new Core_Exception('You can\'t invite the opponents for a public gaming table', 207, Core_Exception::USER);
        }

        //Получаем список приглашенных игроков
        $users = (array)$this->getRequest()->get('users', array());
        //Список игроков, которым отослано приглашение
        $inviteUsers = array();
        //Проверка возможности приглашения, приглашение пользователей
        foreach($users as $userSid) {
            if (!$room->hasUser($userSid) || $room->isUserInGame($userSid)) {
                continue;
            }
            //Получаем данные сессии игрока
            $userSession = $this->_getUserSession($userSid);
            if (!$userSession) {
                continue;
            }
            //Добавляем данные о приглашении в сессию игры
            $this->getGameSession()->addInvite($userSid);
            //Создание объекта уведомления пользователя
            $inviteNotification = new App_Model_Room_Notification_InviteGame();
            $inviteNotification->setUserSid($userSid)
                ->setGameSid($this->getGameSession()->getSid())
                ->setCreatorSid($this->getGameSession()->getCreatorSid())
                ->save();
            //Добавляем пользователя в список приглашенных
            $inviteUsers[$userSid] = array(
                'name' => $userSession->getSocialUser()->getName(),
                'photo' => $userSession->getSocialUser()->getPhoto()
            );
        }

        if (count($inviteUsers)) {
            //Проверка наличия оповещения создателя игры о согласии/отказе приглашений
            $notificationInviteInfo = new App_Model_Room_Notification_InviteInfo();
            $notificationInviteInfo->setUserSid($this->getGameSession()->getCreatorSid());
            $notificationInviteInfo->setGameSid($this->getGameSession()->getSid());
            if (!$notificationInviteInfo->findByData()) {
                //Оповещения нет, создаем его
                $notificationInviteInfo->save();
            }
        }

        //Возвращаем список приглашенных пользователей
        return $inviteUsers;
    }

    /**
     * Получение данных сессии пользователя по ее иднтификатору
     *
     * @param string $sid Идентификатор сессии пользователя
     *
     * @return App_Model_Session_User Если данные сессии не найдены, вернет FALSE
     */
    private function _getUserSession($sid)
    {
        $session = new App_Model_Session_User();
        if ($session->find($sid)) {
            return $session;
        } else {
            return false;
        }
    }
}
