<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 01.03.12
 * Time: 12:34
 *
 * Шаблонный метод добавления игрока за игровой стол
 */
abstract class App_Service_Room_Game_Templates_Join
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
     * Тип пользователя (игрок|наблюдатель)
     *
     * @var bool
     */
    protected $_watcher = false;


    /**
     * __construct
     *
     * @param App_Model_Session_Game|null $game
     * @param App_Model_Session_User|null $user
     * @param bool $watcher Флаг добавления пользователя в качестве наблюдателя
     */
    public function __construct(App_Model_Session_Game $game = null, App_Model_Session_User $user = null, $watcher = false)
    {
        if (null !== $game) {
            $this->setGameSession($game);
        }
        if (null !== $user) {
            $this->setUserSession($user);
        }
        $this->setWatcher($watcher);
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
     * Установка объекта сессии игрового стола
     *
     * @param App_Model_Session_Game $game
     * @return App_Service_Room_Game_Templates_Join
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
     * Установка флага добавления пользователя в качестве наблюдателя
     *
     * @param bool $watch
     */
    public function setWatcher($watch = true)
    {
        $this->_watcher = $watch;
    }

    /**
     * Проверка добавления пользователя в качестве наблюдателя
     *
     * @return bool
     */
    public function isWatcher()
    {
        return $this->_watcher;
    }

    /**
     * Проверка возможности добавления пользователя в игру
     *
     * @abstract
     * @return bool
     * @throws Core_Exception
     */
    abstract public function canJoin();

    /**
     * Метод добавления пользователя в игру
     *
     * @abstract
     * @param int|null $position Позиция места пользователя за игровым столом
     * @throws Core_Exception
     */
    abstract public function joinPlayer($position = null);

    /**
     * Добавление пользователя в чат игры
     *
     * @abstract
     * @param int $userChatType Тип пользователя (игрок|наблюдатель)
     * @throws Core_Exception
     */
    abstract public function addUserInChat($userChatType = Core_Game_Chat_Player::REAL_PLAYER);

    /**
     * Добавление пользователя в игру
     *
     * @param int|null $position Позиция места игрового стола, за которое садится игрок
     * @return void
     * @throws Core_Exception|Exception
     */
    public function join($position = null)
    {
        //Проверка закрытия игрового стола
        if ($this->getGameSession()->getData()->hasEvent(App_Service_Events_Gameclose::name())) {
            //Игровой стол закрыт
            throw new Core_Exception('Game closed', 214);
        }

        //Если в игру добавлен наблюдатель
        if ($this->isWatcher()) {
            //Добавление пользвователя как наблюдателя
            $this->_joinWatcher();
            return;
        }

        //Проверка возможности добавления пользователя в игру
        if (!$this->canJoin()) {
            throw new Core_Exception('Inconsistency to play', 309, Core_Exception::USER);
        }

        //Блокируем сессию игры и получаем ее актуальные данные
        $this->getGameSession()->lockAndUpdate();
        //Проверяем, не занято ли место за столом
        if (null !== $position) {
            $player = $this->getGameSession()->getData()->getPlayersContainer()->getIterator()->getElement($position);
            if ($player) {
                //Место уже занято
                $this->getGameSession()->unlock();
                throw new Core_Exception('The game is already in place', 215, Core_Exception::USER);
            }
        }
        //Блокируем сессию чата и получаем его актуальные данные
        $this->getChatSession()->lockAndUpdate();

        try {
            //Проверка наличия пользователя в списке наблюдателей
            if ($this->getGameSession()->isSpectator($this->getUserSession()->getSid())) {
                //Удаляем пользователя из списка наблюдателей
                $this->getGameSession()->delSpectator($this->getUserSession()->getSid());
            }

            //Добавление пользователя в игру
            $this->joinPlayer($position);

            //Добавление пользователя в чат
            $this->addUserInChat();

            //Сохранение данных сессии игры
            if (!$this->getGameSession()->save()) {
                throw new Core_Exception('Ann error has occurred while saving game session', 202);
            }
        } catch (Exception $e) {
            //Разблокировка сессии игры и чата
            $this->getGameSession()->unlock();
            $this->getChatSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохранение и разблокировка данных игры и чата
        $this->getGameSession()->saveAndUnlock();
        $this->getChatSession()->saveAndUnlock();

        //Удаление пользователя из чата игрового зала
        $this->_deleteUserFromRoomChat();

        //Добавляем данные игры в сессию пользователя
        $this->_setGameLinkToUserSession();

        //Если стол приватный, уведомляем создателя о принятии приглашения текущим пользователем
        if ($this->getGameSession()->getMode() == App_Model_Session_Game::PRIVATE_MODE) {
            $this->_inviteConfirmNotify();
        }
    }

    /**
     * Добавление пользователя за игровой стол в качестве наблюдателя
     *
     * @throws Core_Exception
     * @return void
     */
    private function _joinWatcher()
    {
        //Проверка возможности наблюдения за игрой
        if (!$this->getGameSession()->getSpectator()) {
            Zend_Registry::get('log')->debug(print_r($this->getGameSession()->getInvites(), true), $this->getUserSession()->getSid());
            //Наблюдение запрещено, проверяем есть ли пользователь в списке приглашенных
            if (!$this->getGameSession()->hasInvite($this->getUserSession()->getSid())) {
                //Нет доступа к наблюдению игры
                throw new Core_Exception('Game observe is deny', 210, Core_Exception::USER);
            }
        }
        //Удаление пользователя из чата игрового зала
        $this->_deleteUserFromRoomChat();
        //Блокируем сессию чата и получаем его актуальные данные
        $this->getChatSession()->lockAndUpdate();
        //Добавление пользователя в чат игры
        $this->addUserInChat(Core_Game_Chat_Player::OBSERVER_PLAYER);
        //Сохранение данных чата и разблокировка
        $this->getChatSession()->saveAndUnlock();

        //Получаем текущие данные наблюдатетя (баланс, опыт)
        $api = new Core_Api_DataService_Balance();
        $balance = $api->getUserBalance(
            $this->getUserSession()->getSocialUser()->getId(),
            $this->getUserSession()->getSocialUser()->getNetwork()
        );
        $api = new Core_Api_DataService_Experience();
        $experience = $api->getUserExperience(
            $this->getUserSession()->getSocialUser()->getId(),
            $this->getUserSession()->getSocialUser()->getNetwork(),
            $this->getGameSession()->getData()->getName()
        );
        //Проверка возможности пользователя сесть за игровой стол
        $canSitdown = true;
        if ($this->getGameSession()->getMinBalance() > $balance) {
            $canSitdown = false;
        }
        if ($this->getGameSession()->getMinExperience() > $experience) {
            $canSitdown = false;
        }
        //Добавление наблюдателя за игровой стол
        $this->getGameSession()->lockAndUpdate();
        $this->getGameSession()->addSpectator($this->getUserSession()->getSid(), $canSitdown);
        $this->getGameSession()->saveAndUnlock();

        //Если стол приватный, уведомляем создателя о принятии приглашения текущим пользователем
        if ($this->getGameSession()->getMode() == App_Model_Session_Game::PRIVATE_MODE) {
            $this->_inviteConfirmNotify();
        }
    }

    /**
     * Установка ссылки на игру (идентификатор сессии), в которую присоединился пользователь
     *
     * @return void
     */
    private function _setGameLinkToUserSession()
    {
        $this->getUserSession()->lockAndUpdate();
        $this->getUserSession()->setGameSid($this->getGameSession()->getSid());
        $this->getUserSession()->saveAndUnlock();
    }

    /**
     * Удаление пользователя из чата игрового зала
     */
    private function _deleteUserFromRoomChat()
    {
        //Получаем данные чата игрового зала
        $roomChat = new App_Model_Room_Chat($this->getGameSession()->getName());
        //Проверка наличия пользователя в чате
        if ($roomChat->getChat()->hasPlayer($this->getUserSession()->getSid())) {
            //Лочим и обновляем данные чата
            $roomChat->lockAndUpdate();
            //Удаляем пользователя из чата
            $roomChat->getChat()->dellPlayer($this->getUserSession()->getSid());
            //Сохраняем и разблокируем данные чата
            $roomChat->saveAndUnlock();
        }
    }

    /**
     * Обработка принятия приглашения сесть за приватный игровой стол
     *
     * @return void
     */
    private function _inviteConfirmNotify()
    {
        //Проверка наличия пользователя в списке приглашенных
        if (!$this->getGameSession()->hasInvite($this->getUserSession()->getSid())) {
            //Пользователя нет в списке приглашенных
            return;
        }

        //Удаление оповещение приглашения пользователя
        $inviteNotification = new App_Model_Room_Notification_InviteGame();
        $inviteNotification->setUserSid($this->getUserSession()->getSid());
        $inviteNotification->setGameSid($this->getGameSession()->getSid());
        if ($inviteNotification->findByData()) {
            //Удаление
            $inviteNotification->delete();
        }

        //Получаем объект оповещения создателя о действиях приглашенных
        $notification = new App_Model_Room_Notification_InviteInfo();
        $notification->setUserSid($this->getGameSession()->getCreatorSid());
        $notification->setGameSid($this->getGameSession()->getSid());
        if ($notification->findByData()) {
            $notification->lockAndUpdate();
            //Добаляем текущего пользователя в список согласившихся сесть за стол
            if ($notification->addConfirmUser($this->getUserSession()->getSid())) {
                //Сбрасываем флаг уведомления создателя игры (для обновления оповещения на стороне клиента)
                $notification->resetNotify();
            }
            $notification->saveAndUnlock();
        }
    }

}
