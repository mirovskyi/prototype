<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 22.02.12
 * Time: 17:51
 *
 * Логика создания/получения сессии игрока
 */
class App_Service_Handler_Room_Open_User
{

    /**
     * Данные игрового зала
     *
     * @var App_Model_Room
     */
    protected $_room;

    /**
     * Данные чата игрового зала
     *
     * @var App_Model_Room_Chat
     */
    protected $_roomChat;

    /**
     * Объект данных пользователя соц. сети
     *
     * @var Core_Social_User
     */
    protected $_userInfo;

    /**
     *  Объект сессии игрока
     *
     * @var App_Model_Session_User
     */
    protected $_userSess;


    /**
     * Создание объекта
     *
     * @param App_Model_Room      $room Объект данных игрового зала
     * @param App_Model_Room_Chat $chat
     * @param string $network
     * @param array $userVars
     */
    public function __construct(App_Model_Room $room, App_Model_Room_Chat $chat, $network, array $userVars)
    {
        //Установка объекта игровой комнаты
        $this->setRoom($room);
        //Установка объекта чата игрового зала
        $this->setRoomChat($chat);
        //Создание объекта данных пользователя соц. сети
        $this->setUserInfo(new Core_Social_User($network, $userVars));
    }

    /**
     * Установка объекта данных игровой комнаты
     *
     * @param App_Model_Room $room
     */
    public function setRoom(App_Model_Room $room)
    {
        $this->_room = $room;
    }

    /**
     * Получение объекта данных игровой комнаты
     *
     * @return App_Model_Room
     */
    public function getRoom()
    {
        return $this->_room;
    }

    /**
     * Установка объекта данных чата игрового зала
     *
     * @param App_Model_Room_Chat $chat
     */
    public function setRoomChat(App_Model_Room_Chat $chat)
    {
        $this->_roomChat = $chat;
    }

    /**
     * Получение объекта данных чата игрового зала
     *
     * @return App_Model_Room_Chat
     */
    public function getRoomChat()
    {
        return $this->_roomChat;
    }

    /**
     * Установка объекта данных пользователя соц. сети
     *
     * @param Core_Social_User $userInfo
     */
    public function setUserInfo($userInfo)
    {
        $this->_userInfo = $userInfo;
    }

    /**
     * Получение объекта данных пользователя соц. сети
     *
     * @return Core_Social_User
     */
    public function getUserInfo()
    {
        return $this->_userInfo;
    }

    /**
     * Установка объекта сессии игрока
     *
     * @param App_Model_Session_User $session
     */
    public function setUserSession(App_Model_Session_User $session)
    {
        $this->_userSess = $session;
    }

    /**
     * Получение/создание сессии пользователя
     *
     * @param string $key [Optional] Идентификатор сессии
     * @return App_Model_Session_User
     */
    public function getUserSession($key = null)
    {
        if (null === $this->_userSess) {
            //Создание новой сессии игрока
            $session = $this->_createUserSession($key);
            //Добавление идентификатора сессии игрока в игровой зал
            $this->_addUserInRoom($session->getSid());
            //Установка сессии игрока
            $this->setUserSession($session);
        }

        return $this->_userSess;
    }

    /**
     * Создание сессии игрока
     *
     * @param string $key [Optional] Идентификатор сессии
     * @return App_Model_Session_User
     * @throws Core_Exception
     */
    protected function _createUserSession($key = null)
    {
        //Проверка наличия сессии игрока в зале
        $session = $this->_findUserSessionInRoom();
        if (false !== $session) {
            //Удаление существующией сессии пользователя
            $this->_deleteUserSession($session);
        }

        //Создание идентификатра сессии (ключа записи сессии)
        if (null == $key) {
            $key = Core_Session::createSID(Core_Session::USER_NAMESPACE);
        }
        //Создание сессии пользователя
        $session = new App_Model_Session_User();
        $session->setKey($key);
        $session->setNetwork($this->getUserInfo()->getNetwork());
        $session->setIp($_SERVER['REMOTE_ADDR']);
        $session->setData($this->getUserInfo()->getParams());
        //Получение данных пользователя из сервера балансов
        $userData = $this->_getUserData();
        //Установка идентификатора сессии пользователя по идентификатору системе приложения
        $session->setSid('u' . $userData['uid']); //Добавление префикса 'u' для формирования идентификатора сессии в виде строки (string)
        //Добавление купленных пользователем товаров
        $session->setItems($this->_getUserItems());
        //Сохраняем данные сессии пользователя в хранилище
        if (!$session->save()) {
            throw new Core_Exception('Ann error has occurred while saving user session', 101);
        }

        //Возвращаем объект сессии игрока
        return $session;
    }

    /**
     * Поиск существующей сессии пользователя
     *
     * @return App_Model_Session_User|bool
     */
    protected function _findUserSessionInRoom()
    {
        //Данные текущего игрока
        $id = $this->getUserInfo()->getId();
        $network = $this->getUserInfo()->getNetwork();
        //Проверяем данные каждого игрока в зале
        foreach($this->getRoom()->getUsersSid() as $userSid) {
            //Получаем данные пользователя
            $user = new App_Model_Session_User();
            if (!$user->find($userSid)) {
                continue;
            }
            //Проверка соответствия текущему пользователю
            $socialUser = $user->getSocialUser();
            if ($socialUser->getId() == $id && $socialUser->getNetwork() == $network) {
                //Возвращаем найденую сессию пользователя
                return $user;
            }
        }

        //Сессии пользователя нет в игровом зале
        return false;
    }

    /**
     * Удаление сессии пользователя
     *
     * @param App_Model_Session_User $userSession
     */
    protected function _deleteUserSession(App_Model_Session_User $userSession)
    {
        //Проверка наличия игрока за игровым столом
        if ($this->getRoom()->isUserInGame($userSession->getSid())) {
            //Получаем объект сессии игры
            $gameSession = new App_Model_Session_Game();
            if ($gameSession->find($this->getRoom()->getUserGame($userSession->getSid()))) {
                //Удаление игрока из игрового стола
                $deleteUserImplementation = App_Service_Room_Game_DeleteUser::factory(
                    $this->getRoom()->getNamespace(),
                    $gameSession,
                    $userSession
                );
                $deleteUserImplementation->delete();
            }
        }

        //Удаление пользователя из зала
        $this->getRoom()->lockAndUpdate();
        $this->getRoom()->delUser($userSession->getSid());
        $this->getRoom()->saveAndUnlock();

        //Удаление пользователя из чата игрового зала
        if ($this->getRoomChat()->getChat()->hasPlayer($userSession->getSid())) {
            $this->getRoomChat()->lockAndUpdate();
            $this->getRoomChat()->getChat()->dellPlayer($userSession->getSid());
            $this->getRoomChat()->saveAndUnlock();
        }

        //Установка флага удаления сессии игрока
        $userSession->lock();
        $userSession->setDeleted();
        $userSession->saveAndUnlock();

        //Добавление сессии игрока в "мусор", для дальнейшего удаления
        $trush = new App_Model_Trash($this->getRoom()->getNamespace());
        $trush->lockAndUpdate();
        $trush->addItem($userSession->getSid());
        $trush->saveAndUnlock();
    }

    /**
     * Добавление данных пользователя в игровой зал
     *
     * @param string $userSessionId Идентификатор сессии пользователя
     */
    protected function _addUserInRoom($userSessionId)
    {
        //Блокируем и олучаем актуальные данные игрового зала
        $this->getRoom()->lockAndUpdate();
        //Добавляем пользователя в зал
        $this->getRoom()->addUser($userSessionId);
        //Сохраняем и разблокируем данные игрового зала
        $this->getRoom()->saveAndUnlock();
    }

    /**
     * Получение данных пользователя из сервера "балансов"
     *
     * @return array
     */
    protected function _getUserData()
    {
        //API получения данных пользователя
        $api = new Core_Api_DataService_Info();
        //Получение данных пользователя
        return $api->getUserInfo($this->getUserInfo()->getId(), $this->getUserInfo()->getNetwork());
    }

    /**
     * Получение списка товаров пользователя
     *
     * @return array
     */
    protected function _getUserItems()
    {
        //API получения данных пользователя
        $api = new Core_Api_DataService_Info();
        //Получение данных пользователя
        $info = $api->getUserInfo($this->getUserInfo()->getId(), $this->getUserInfo()->getNetwork());
        //Отдаем список товаров
        if (isset($info['items'])) {
            return $info['items'];
        } else {
            return array();
        }
    }


}
