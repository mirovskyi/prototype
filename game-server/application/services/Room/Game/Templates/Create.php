<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 29.02.12
 * Time: 16:37
 *
 * Шаблонный метод создания игры
 */
abstract class App_Service_Room_Game_Templates_Create
{

    /**
     * Параметры создаваемого игрового стола
     *
     * @var array
     */
    protected $_gameParams = array();

    /**
     * Объект модели сессии пользователя
     *
     * @var App_Model_Session_User
     */
    protected $_userSession;


    /**
     * __construct
     *
     * @param App_Model_Session_User|null $user Объект данных сессии пользователя (создателя игрового стола)
     * @param array|null $gameParams Параметры игры
     */
    public function __construct(App_Model_Session_User $user = null, array $gameParams = null)
    {
        if (null !== $user) {
            $this->setUserSession($user);
        }
        if (is_array($gameParams)) {
            $this->setGameParams($gameParams);
        }
    }

    /**
     * Установка параметров игрового стола
     *
     * @param array $params
     * @return App_Service_Room_Game_Templates_Create
     */
    public function setGameParams(array $params)
    {
        $this->_gameParams = $params;
        return $this;
    }

    /**
     * Получение параметров игрового стола
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_gameParams;
    }

    /**
     * Установка значения параметра игрового стола
     *
     * @param string $name
     * @param mixed $value
     * @return App_Service_Room_Game_Templates_Create
     */
    public function setParam($name, $value)
    {
        $this->_gameParams[$name] = $value;
        return $this;
    }

    /**
     * Получение значения параметра игрового стола
     *
     * @param string $name Название параметра
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (isset($this->_gameParams[$name])) {
            return $this->_gameParams[$name];
        } else {
            return $default;
        }
    }

    /**
     * Установка объекта модели сессии
     *
     * @param App_Model_Session_User $userSession
     * @return App_Service_Room_Game_Templates_Create
     */
    public function setUserSession(App_Model_Session_User $userSession)
    {
        $this->_userSession = $userSession;
        return $this;
    }

    /**
     * Получение объекта модели сессии игрока (создателя игрового стола)
     *
     * @return App_Model_Session_User
     * @throws Core_Exception
     */
    public function getUserSession()
    {
        return $this->_userSession;
    }

    /**
     * Проверка возможности создания игры пользователем (валидность параметров игры)
     *
     * @abstract
     * @return bool
     */
    abstract public function valid();

    /**
     * Создание объекта игры
     *
     * @abstract
     * @return Core_Game_Abstract
     */
    abstract public function createGameObject();

    /**
     * Создание объекта чата
     *
     * @abstract
     * @return Core_Game_Chat
     */
    abstract public function createChat();

    /**
     * Создание сессии игры
     *
     * @abstract
     * @param Core_Game_Abstract $gameObject
     * @return App_Model_Session_Game
     */
    abstract public function createGameSession(Core_Game_Abstract $gameObject);

    /**
     * Создвние объекта модели сессии чата игры
     *
     * @abstract
     * @param string $gameSid Идентификатор сессии игры
     * @param Core_Game_Chat $chat
     * @return App_Model_Session_GameChat
     * @throws Core_Exception
     */
    abstract public function createChatSession($gameSid, Core_Game_Chat $chat);

    /**
     * Метод создания игры
     * Возвращает объект данных игрового стола в игровом зале
     *
     * @throws Core_Exception
     * @return App_Model_Session_Game
     */
    public function create()
    {
        //Проверка возможности создания игры
        if (!$this->valid()) {
            throw new Core_Exception('Inconsistency to play', 309, Core_Exception::USER);
        }
        //Создание объекта игры
        $gameObject = $this->createGameObject();
        //Установка стартовой суммы ставки
        $gameObject->setStartBet($gameObject->getBet());
        //Проверка наличия у пользователя необходимой суммы на балансе
        if ($this->_getUserBalance() < $gameObject->getStartBet()) {
            throw new Core_Exception(
                'Failed to create game. User balance should be more than game bet',
                304,
                Core_Exception::USER
            );
        }

        //Создание объекта модели сессии игры
        $gameSession = $this->createGameSession($gameObject);
        //Если стол приватный, создаем приглашния оппонентам
        if ($gameSession->getMode() == App_Model_Session_Game::PRIVATE_MODE) {
            $this->_inviteUsers($gameSession);
        }
        //Запись данных сессии игры в реестр
        Core_Session::getInstance()->set(Core_Session::GAME_NAMESPACE, $gameSession);
        //Создание чата
        $chat = $this->createChat();
        //Создание объекта модели сессии чата
        $this->createChatSession($gameSession->getSid(), $chat);

        //Добавление ссылки на игру в данных сессии пользователя
        $this->getUserSession()->lockAndUpdate();
        $this->getUserSession()->setGameSid($gameSession->getSid());
        $this->getUserSession()->saveAndUnlock();

        //Удаление пользователя из чата игрового зала
        $this->_deleteUserFromRoomChat($gameObject->getName());

        //Отдаем данные игрового стола
        return $gameSession;
    }

    /**
     * Приглашение пользователей за приватный игровой стол
     *
     * @param App_Model_Session_Game $session
     * @return void
     */
    protected function _inviteUsers(App_Model_Session_Game $session)
    {
        //Получение списка приглашенных пользователей
        $invite = $this->getParam('invite', array());
        if (!isset($invite['sid'])) {
            return;
        }
        $inviteUsers = (array)$invite['sid'];

        //Приглашение каждого пользователя
        foreach($inviteUsers as $sid) {
            //Добавление в список приглашенных
            $session->addInvite($sid);
            //Создание оповещения пользователя о приглашении
            $notification = new App_Model_Room_Notification_InviteGame();
            $notification->setUserSid($sid);
            $notification->setGameSid($session->getSid());
            $notification->setCreatorSid($this->getUserSession()->getSid());
            $notification->save();
        }
        //Сохраняем данные сессии игры
        $session->save();

        //Создание объкта оповещения создателя о соглашении отказе оппонентов сесть за стол
        $notification = new App_Model_Room_Notification_InviteInfo();
        $notification->setUserSid($this->getUserSession()->getSid());
        $notification->setGameSid($session->getSid());
        $notification->save();
    }

    /**
     * Удаление пользователя из чата игрового зала
     *
     * @param string $gameName Имя игры
     */
    private function _deleteUserFromRoomChat($gameName)
    {
        //Получаем данные чата игрового зала
        $roomChat = new App_Model_Room_Chat($gameName);
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
     * Получение текущего баланса пользователя
     *
     * @return bool|int
     */
    private function _getUserBalance()
    {
        $api = new Core_Api_DataService_Balance();
        return $api->getUserBalance(
            $this->getUserSession()->getSocialUser()->getId(),
            $this->getUserSession()->getSocialUser()->getNetwork()
        );
    }

}
