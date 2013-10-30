<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 02.03.12
 * Time: 15:03
 *
 * Дефолтная реализация алгоритма создания игры
 */
class App_Service_Room_Game_Implementations_Create_Default
    extends App_Service_Room_Game_Templates_Create
{

    /**
     * Текущий баланс пользователя
     *
     * @var int
     */
    protected $_balance;

    /**
     * Проверка возможности создания игры пользователем (валидность параметров игры)
     *
     * @throws Core_Exception
     * @return bool
     */
    public function valid()
    {
        $bet = $this->getParam('bet');
        if ($bet && $bet > $this->_getUserBalance()) {
            throw new Core_Exception('Failed to create game. User balance should be more than game bet',
                                     304, Core_Exception::USER);
        }

        //Проверка соответствия параметра минимального баланса оппонентов с суммой ставки
        if ($this->getParam('mb') < $this->getParam('bet')) {
            //Корректируем настроку минимального баланса
            $this->setParam('mb', $this->getParam('bet'));
        }

        return true;
    }

    /**
     * Создание объекта игры
     *
     * @return Core_Game_Abstract
     */
    public function createGameObject()
    {}

    /**
     * Создание объекта чата
     *
     * @return Core_Game_Chat
     */
    public function createChat()
    {
        //Создание объекта чата игры
        $chat = new Core_Game_Chat();

        //Получение данных пользователя соц. сети
        $userInfo = $this->getUserSession()->getSocialUser();
        //Создание данных пользователя в чате
        $chatPlayer = new Core_Game_Chat_Player();
        $chatPlayer->setSid($this->getUserSession()->getSid())
                   ->setType(Core_Game_Chat_Player::REAL_PLAYER)
                   ->setName($userInfo->getName());

        //Добавляем пользователя в чат
        $chat->addPlayer($chatPlayer);

        //Возвращаем объект чата
        return $chat;
    }

    /**
     * Создание сессии игры
     *
     * @param Core_Game_Abstract $gameObject
     * @throws Core_Exception
     * @return App_Model_Session_Game
     */
    public function createGameSession(Core_Game_Abstract $gameObject)
    {
        //Создание объекта модели сессии игры
        $session = new App_Model_Session_Game();
        //Генерация уникального идентификатора сессии игры
        $sid = Core_Session::createSID(Core_Session::GAME_NAMESPACE);
        //Проверка наличия у пользователя-создателя купленного VIP статуса
        $isVip = $this->getUserSession()->hasItem(Core_Shop_Items::VIP);
        if ($isVip) {
            //Проверка наличия присланного параметра VIP
            if (null !== $this->getParam('vip')) {
                //Установка полученного значения
                $isVip = $this->getParam('vip');
            }
        }
        //Установка данных сессии игры
        $session->setSid($sid)
                ->setMode($this->getParam('m', App_Model_Session_Game::PUBLIC_MODE))
                ->setSpectator($this->getParam('o', App_Model_Session_Game::SPECTATOR_ALLOW))
                ->setMinBalance($this->getParam('mb'))
                ->setMinExperience($this->getParam('me'))
                ->setFirstMove($this->getParam('fm', 0))
                ->setCreatorSid($this->getUserSession()->getSid())
                ->setVip($isVip)
                ->setName($gameObject->getName())
                ->setData($gameObject);
        //Сохраняем данные сессии
        if (!$session->save()) {
            throw new Core_Exception('Ann error has occurred while saving game session', 202);
        }

        //Возвращаем объект сессии игры
        return $session;
    }

    /**
     * Создвние объекта модели сессии чата игры
     *
     * @param string $gameSid Идентификатор сессии игры
     * @param Core_Game_Chat $chat
     * @return App_Model_Session_GameChat
     * @throws Core_Exception
     */
    public function createChatSession($gameSid, Core_Game_Chat $chat)
    {
        //Создание объекта модели сессии чата
        $session = new App_Model_Session_GameChat();
        //Установка данных сессии
        $session->setSid($gameSid)
                ->setChat($chat);
        //Сохранение данных сессии чата
        if (!$session->save()) {
            throw new Core_Exception('Ann error has occurred while saving chat session', 271);
        }

        //Возвращаем объект сессии чата
        return $session;
    }

    /**
     * Получение баланса пользователя
     *
     * @return int|bool
     */
    protected function _getUserBalance()
    {
        if (null === $this->_balance) {
            //Сервис балансов
            $balanceApi = new Core_Api_DataService_Balance();
            //Запрос получения баланса пользователя
            $this->_balance = $balanceApi->getUserBalance(
                $this->getUserSession()->getSocialUser()->getId(),
                $this->getUserSession()->getSocialUser()->getNetwork()
            );
        }

        //Возвращаем баланс пользователя
        return $this->_balance;
    }
}
