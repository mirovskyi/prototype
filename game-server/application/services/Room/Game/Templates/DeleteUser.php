<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 26.03.12
 * Time: 10:34
 *
 * Шаблонный метод удаления пользователя из игры
 */
abstract class App_Service_Room_Game_Templates_DeleteUser
{

    /**
     * Объект модели сессии игрока
     *
     * @var App_Model_Session_User
     */
    protected $_userSession;

    /**
     * Объект модели сессии игры
     *
     * @var App_Model_Session_Game
     */
    protected $_gameSession;

    /**
     * Объект модели сессии чата игры
     *
     * @var App_Model_Session_GameChat
     */
    protected $_gameChatSession;


    /**
     * __construct
     *
     * @param App_Model_Session_Game|null $game
     * @param App_Model_Session_User|null $user
     */
    public function __construct(App_Model_Session_Game $game = null, App_Model_Session_User $user = null)
    {
        if (null !== $game) {
            $this->setGameSession($game);
            //Добавление сессии игры в реестр (слушатели изменения данных игры берут сессию из реестра)
            Core_Session::getInstance()->set(Core_Session::GAME_NAMESPACE, $game);
        }
        if (null !== $user) {
            $this->setUserSession($user);
        }
    }

    /**
     * Установка объекта модели сессии игрока
     *
     * @param App_Model_Session_User $userSession
     * @return App_Service_Room_Game_Templates_Join
     */
    public function setUserSession(App_Model_Session_User $userSession)
    {
        $this->_userSession = $userSession;
        return $this;
    }

    /**
     * Получение объекта модели сессии игрока
     *
     * @return App_Model_Session_User
     * @throws Core_Exception
     */
    public function getUserSession()
    {
        return $this->_userSession;
    }

    /**
     * Установка объекта модели сессии игры
     *
     * @param App_Model_Session_Game $game
     * @return App_Service_Room_Game_Templates_DeleteUser
     */
    public function setGameSession(App_Model_Session_Game $game)
    {
        $this->_gameSession = $game;
        return $this;
    }

    /**
     * Получение объета модели сессии игры
     *
     * @return App_Model_Session_Game
     * @throws Core_Exception
     */
    public function getGameSession()
    {
        return $this->_gameSession;
    }

    /**
     * Получение объекта модели сессии чата игры
     *
     * @return App_Model_Session_GameChat
     * @throws Core_Exception
     */
    public function getChatSession()
    {
        if (null === $this->_gameChatSession) {
            //Идентификатор сессии игры
            $gameSid = $this->getGameSession()->getSid();
            //Объект модели сессии чата игы
            $session = new App_Model_Session_GameChat();
            //Поиск данных сессии в хранилище
            $session->find($gameSid);
            if (null == $session->getSid()) {
                throw new Core_Exception('Chat session was not found', 270);
            }
            //Установка объекта модели сессии чата игры
            $this->_gameChatSession = $session;
        }

        return $this->_gameChatSession;
    }

    /**
     * Удаление пользователя из игрового стола и игровой сессии
     *
     * @abstract
     * @return void
     */
    abstract public function deleteUserFromGame();

    /**
     * Удаление пользователя из чата игры
     *
     * @abstract
     * @return void
     */
    abstract public function deleteUserFromChat();

    /**
     * Удаление пользователя из игры
     *
     * @throws Exception
     */
    public function delete()
    {
        //Проверка наличия игрока за игровым столом
        $players = $this->getGameSession()->getData()->getPlayersContainer();
        if (null !== $players->getPlayer($this->getUserSession()->getSid())) {
            //Блокируем и обновляем данные игровой сессии
            $this->getGameSession()->lockAndUpdate();
            //удаляем пользователя из игры
            try {
                $this->deleteUserFromGame();
            } catch (Exception $e) {
                //Разблокируем данные игры
                $this->getGameSession()->unlock();
                //Выбрасываем исключение
                throw $e;
            }
            //Если из-за стола вышел его создатель - игра закрыта
            if ($this->getGameSession()->getCreatorSid() == $this->getUserSession()->getSid()) {
                //Создание события закрытия игрового стола
                $this->_closeGame();
            }
            //Сохраняем и разблокируем данные игры
            $this->getGameSession()->saveAndUnlock();
        } else {
            //Проверка наличия пользователя за игровым столом в качестве наблюдателя
            if ($this->getGameSession()->isSpectator($this->getUserSession()->getSid())) {
                //Удаляем пользователя из списка наблюдателей
                $this->getGameSession()->lockAndUpdate();
                $this->getGameSession()->delSpectator($this->getUserSession()->getSid());
                $this->getGameSession()->saveAndUnlock();
            }
        }

        //Если стол приватный - удаляем игрока из списка приглашенных
        if ($this->getGameSession()->getMode() == App_Model_Session_Game::PRIVATE_MODE) {
            //Удаление из списка приглашенных
            $this->getGameSession()->delInvite($this->getUserSession()->getSid());
            //Оповещение создателя игры об отказе от приглашения (если игра еще не началась, после начала оповещение удаляется)
            $this->_inviteDeclineNotify();
        }

        //Удаляем игрока из чата
        $this->getChatSession()->lockAndUpdate();
        try {
            $this->deleteUserFromChat();
        } catch (Exception $e) {
            //Разблокируем данные чата
            $this->getChatSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }
        //Сохраняем и разблокируем данные чата
        $this->getChatSession()->saveAndUnlock();

        //Удаляем ссылку на игру в сессии пользователя
        $this->getUserSession()->lockAndUpdate();
        $this->getUserSession()->clearGameSid();
        $this->getUserSession()->saveAndUnlock();
    }

    /**
     * Создание события закрытия игрового стола
     *
     * @return void
     */
    private function _closeGame()
    {
        //Объект данных игры
        $game = $this->getGameSession()->getData();
        //Проверка наличия события закрытия игрового стола
        if ($game->hasEvent(App_Service_Events_Gameclose::name())) {
            //Удаление предыдущего события, т.к. может быть только одно событие этого типа
            //(событие закрытия игры так же может быть для игроков у которых недостаточно средств для продолжения)
            $game->deleteEvent(App_Service_Events_Gameclose::name());
        }
        //Окончание игры
        $game->setStatus(Core_Game_Abstract::STATUS_FINISH);
        //Добавление события закрытия игрового стола
        $game->addEvent(new App_Service_Events_Gameclose());

    }

    /**
     * Обработка отказа от приглашения сесть за приватный игровой стол
     */
    private function _inviteDeclineNotify()
    {
        //Получение объекта оповещения создателя игры о действиях оппонента
        $notification = new App_Model_Room_Notification_InviteInfo();
        $notification->setUserSid($this->getGameSession()->getCreatorSid());
        $notification->setGameSid($this->getGameSession()->getSid());
        if ($notification->findByData()) {
            //Если вышел сам создатель игры, удаляем оповещение
            if ($this->getUserSession()->getSid() == $this->getGameSession()->getCreatorSid()) {
                $notification->delete();
            }
            //Добавление пользователя в список отказавшихся
            elseif ($notification->addDeclineUser($this->getUserSession()->getSid())) {
                //Сброс флага уведомления создателя (для обновления оповещения на стороне клиента)
                $notification->resetNotify();
            }
        }
    }

}
