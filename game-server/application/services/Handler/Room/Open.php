<?php

class App_Service_Handler_Room_Open extends App_Service_Handler_Abstract
{

    /**
     * Системное имя игры
     *
     * @var string
     */
    protected $_game;

    /**
     * Объект модели данных игрового зала
     *
     * @var App_Model_Room
     */
    protected $_room;

    /**
     * Объект модели чата игрового зала
     *
     * @var App_Model_Room_Chat
     */
    protected $_roomChat;


    /**
     * Установка системного имени игры
     *
     * @param string $game
     * @return App_Service_Handler_Room_Open
     */
    public function setGameName($game)
    {
        $this->_game = $game;
        return $this;
    }

    /**
     * Получение системного имени игры
     *
     * @return string
     */
    public function getGameName()
    {
        if (null === $this->_game) {
            $this->setGameName($this->getRequest()->get('game', null));
        }
        return $this->_game;
    }

    /**
     * Получение данных игрового зала
     *
     * @return App_Model_Room
     */
    public function getRoom()
    {
        if (null === $this->_room) {
            $this->_room = new App_Model_Room($this->getGameName());
        }

        return $this->_room;
    }

    /**
     * Получение данных чата игрового зала
     *
     * @param bool $init Флаг получения данных чата при инициализации объекта
     *
     * @return App_Model_Room_Chat
     */
    public function getRoomChat($init = true)
    {
        if (null === $this->_roomChat) {
            $this->_roomChat = new App_Model_Room_Chat($this->getGameName(), $init);
        }

        return $this->_roomChat;
    }

    /**
     * Обработка запроса открытия игровго зала
     *
     * @param string|null $game [optional] Системное имя игры
     * @return string
     */
    public function handle($game = null)
    {
        //Установка имени игры
        if (null !== $game) {
            $this->setGameName($game);
        }

        //Если передана сессия игрока - вход через платформу igrok
        if ($this->getRequest()->get('usersession')) {
            //Получаем имя платформы
            $service = Core_Social_Config::get('htmlServiceName');
            $this->getRequest()->set('service', $service);
            //Получение данных пользователя по идентификатору сессии лобби
            $api = new Core_Api_DataService_User();
            $userData = $api->session($this->getRequest()->get('usersession'));
            //Установка данных пользователя
            $this->getRequest()->set('vars', $userData);
        }

        //Создание сессии пользователя
        $openUserModel = new App_Service_Handler_Room_Open_User(
            $this->getRoom(),
            $this->getRoomChat(),
            $this->getRequest()->get('service'),
            $this->getRequest()->get('vars', array()));
        $userSess = $openUserModel->getUserSession($this->getRequest()->get('usersession'));

        //Установка сессии пользователя в реестр
        Core_Session::getInstance()->set(Core_Session::USER_NAMESPACE, $userSess);

        //Проверка наличия флага быстрой игры
        if (!$this->getRequest()->get('quickstart', false)) {
            //Добавляем пользователя в чат зала
            $this->_addUserToRoomChat($userSess);
            //Возвращаем ответ с данными игрового зала
            return $this->_getRoomResponse($userSess);
        }

        //Создание "быстрой" игры
        $quickGameModel = new App_Service_Handler_Room_Open_QuickGame(
            $this->getRoom(),
            $userSess,
            $this->getGameName()
        );
        $gameSess = $quickGameModel->createQuickGame();

        //Возвращаем ответ с данными об игре
        return $this->_getGameResponse($userSess, $gameSess);
    }

    /**
     * Добавлене пользователя в чат игрового зала
     *
     * @param App_Model_Session_User $userSess
     */
    protected function _addUserToRoomChat(App_Model_Session_User $userSess)
    {
        //Лочим чат и полчаем актуальные данные
        $this->getRoomChat(false)->lockAndUpdate();
        //Создание объекта участника чата
        $chatPlayer = new Core_Game_Chat_Player(array(
            'name' => $userSess->getSocialUser()->getName(),
            'sid' => $userSess->getSid(),
            'type' => Core_Game_Chat_Player::OBSERVER_PLAYER
        ));
        //Добавляем пользователя
        $this->getRoomChat()->getChat()->addPlayer($chatPlayer);
        //Сохраняем данные чата
        $this->getRoomChat()->saveAndUnlock();
    }

    /**
     * Получение данных ответа на открытие игры в режиме "Быстрый старт"
     *
     * @param App_Model_Session_User $userSess
     * @param App_Model_Session_Game $gameSess
     * @return string
     */
    protected function _getGameResponse(App_Model_Session_User $userSess, App_Model_Session_Game $gameSess)
    {
        //Установка сессий игры и пользователя в обработчик
        $this->setUserSession($userSess);
        $this->setGameSession($gameSess);

        //Передача данных игрового стола в шаблон
        $this->_assignViewGameData();

        //Флаг открытия игрового стола
        $this->view->assign('isOpen', true);

        //Отдаем данные шаблона игры
        $method = $this->getGameName() . '/update';
        return $this->view->render($method);
    }

    /**
     * Получение данных ответа на открытие игровой комнаты
     *
     * @param App_Model_Session_User $userSess
     * @return string
     */
    protected function _getRoomResponse(App_Model_Session_User $userSess)
    {
        //Получаем данные игровых столов
        $roomService = new App_Service_Room($this->getRoom());
        $games = $roomService->getGamesInfo($userSess);

        //Отдаем данные игровой комнаты и пользователя в шаблон
        $this->view->assign(array(
            'userSess' => $userSess,
            'games' => $games,
            'user' => $userSess->getSocialUser(),
            'timer' => intval($this->_hasTimer($userSess)),
            'vip' => intval($this->_hasVip($userSess)),
            'chat' => $this->getRoomChat()->getChat()->saveXml($userSess->getSid())
        ));
        return $this->view->render();
    }

    /**
     * Проверка наличия у пользователя установки настройки времени партии при создании игры
     *
     * @param App_Model_Session_User $userSess
     * @return bool
     */
    private function _hasTimer(App_Model_Session_User $userSess)
    {
        //Проверка наличия шахматных часов у пользователя
        return $userSess->hasItem(Core_Shop_Items::CHESS_CLOCK);
    }

    /**
     * Проверка наличия у пользователя товара с возможностью усановки настроек при создании игрового стола
     *
     * @param App_Model_Session_User $userSess
     * @return bool
     */
    private function _hasVip(App_Model_Session_User $userSess)
    {
        //Список товаров дающих возможность пользователю установки настроек игрового стола
        $vipItems = array(
            Core_Shop_Items::CHESS_BOARD,
            Core_Shop_Items::FILLER_BOARD,
            Core_Shop_Items::CARDS,
            Core_Shop_Items::BACKGAMMON_BOARD,
            Core_Shop_Items::DEALER,
            Core_Shop_Items::POOL,
        );
        //Проверка применения товара в данном игровом зале и наличия товара у пользователя
        foreach ($vipItems as $item) {
            if (Core_Shop_Items::hasItem($item) && Core_Shop_Items::isUseInGame($item, $this->getGameName())) {
                return true;
            }
        }

        return false;
    }
}