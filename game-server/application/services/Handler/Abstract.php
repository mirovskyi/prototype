<?php

/**
 * Description of Abstract
 *
 * @author aleksey
 */
abstract class App_Service_Handler_Abstract 
{
    
    /**
     * Объект запроса
     * 
     * @var Core_Protocol_Request
     */
    protected $_request;

    /**
     * Объект шаблона
     *
     * @var Core_View
     */
    public $view;
    
    /**
     * Объект сессии игрока
     *
     * @var App_Model_Session_User
     */
    protected $_userSess;
    
    /**
     * Объект сессии игры
     *
     * @var App_Model_Session_Game
     */
    protected $_gameSess;
    
    
    /**
     * __construct
     * 
     * @param Core_Protocol_Request $request
     */
    public function __construct(Core_Protocol_Request $request)
    {
        //Установка объекта запроса
        $this->setRequest($request);

        //Инициализация данных сессии
        $this->_initUserSession();
        $this->_initGameSession();

        //Инициализация объекта шаблона
        $options = Core_Server::getInstance()->getOption('view');
        $this->setView($this->_initView($options));

        //Инициализация
        $this->init();
    }

    /**
     * Инициализация обработчика
     *
     * @return void
     */
    public function init()
    {}
    
    /**
     * Метод установки объекта запроса
     * 
     * @param Core_Protocol_Request $request
     * @return App_Service_Handler_Abstract
     */
    public function setRequest(Core_Protocol_Request $request)
    {
        $this->_request = $request;
        return $this;
    }
    
    /**
     * Метод получения объекта запроса
     * 
     * @return Core_Protocol_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Установка объекта модели сессии пользователя
     *
     * @param App_Model_Session_User $session
     * @return App_Service_Handler_Abstract
     */
    public function setUserSession(App_Model_Session_User $session)
    {
        $this->_userSess = $session;
        return $this;
    }

    /**
     * Метод получения объекта сессии игрока
     *
     * @throws Core_Protocol_Exception
     * @return App_Model_Session_User
     */
    public function getUserSession()
    {
        if (null === $this->_userSess) {
            throw new Core_Protocol_Exception('User session was not found ' . $this->getRequest()->get('usersession'), 103);
        }

        return $this->_userSess;
    }

    /**
     * Проверка наличия сессии пользователя
     *
     * @return bool
     */
    public function hasUserSession()
    {
        return null !== $this->_userSess;
    }

    /**
     * Установка объекта модели сессии игры
     *
     * @param App_Model_Session_Game $session
     * @return App_Service_Handler_Abstract
     */
    public function setGameSession(App_Model_Session_Game $session)
    {
        $this->_gameSess = $session;
        return $this;
    }

    /**
     * Метод получения объекта сессии игры
     *
     * @throws Core_Protocol_Exception
     * @return App_Model_Session_Game
     */
    public function getGameSession()
    {
        if (null === $this->_gameSess) {
            throw new Core_Protocol_Exception('Game session was not found ' . $this->getRequest()->get('gamesession'), 104);
        }

        return $this->_gameSess;
    }

    /**
     * Проверка наличия сессии игры
     *
     * @return bool
     */
    public function hasGameSession()
    {
        return null !== $this->_gameSess;
    }

    /**
     * Установка объекта шаблона
     *
     * @param Core_View $view
     * @return App_Service_Handler_Abstract
     */
    public function setView(Core_View $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Получение объекта шаблона
     *
     * @return Core_View
     */
    public function getView()
    {
        return $this->view;
    }
    
    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    abstract public function handle();

    /**
     * Инициализация объекта шаблона
     *
     * @param array|null $options
     * @return Core_View
     */
    protected function _initView($options)
    {
        if (is_array($options)) {
            //Инициализация объекта вида
            if (isset($options['format'])) {
                $format = $options['format'];
                $viewClass = 'Core_View_' . ucfirst($format);
                if (!class_exists($viewClass)) {
                    $viewClass = 'Core_View';
                }
                $view = new $viewClass();
                unset($options['format']);
            } else {
                $view = new Core_View();
            }

            //Установка настроек
            $view->setOptions($options);
        } else {
            $view = new Core_View();
        }

        //Формирование пути к шаблону
        $template = $this->getRequest()->getHandlerName() . DIRECTORY_SEPARATOR
                    . $this->getRequest()->getMethod();

        //Установка пути к шаблону
        $view->setTemplate($template);

        return $view;
    }

    /**
     * Инициализация сессии пользователя
     */
    protected function _initUserSession()
    {
        //Проверка наличия в запросе идентификатора сессии пользователя
        $sid = $this->getRequest()->get('usersession');
        if (null !== $sid) {
            //Создание объекта модели сессии пользователя
            $session = new App_Model_Session_User();
            //Попытка получить данные сессии
            if ($session->findOnlyByKey($sid)) {
                //Если у сессии установлен флаг удаления, генерим соответствующую ошибку
                //Флаг удаления ставится для оповещения о создании второй сессии игрока (первая сессия удаляется)
                if ($session->isDeleted()) {
                    throw new Core_Exception('The player created another session. Current session has deleted',
                                             106, Core_Exception::USER);
                }
                //Проверка соответствия IP адреса пользователя (защита от подмены сессии в запросе)
                if ($session->getIp() != $_SERVER['REMOTE_ADDR']) {
                    throw new Core_Exception('Lock session spoofing', 111);
                }
                //Запись модели данных сессии в реестр
                Core_Session::getInstance()->set(Core_Session::USER_NAMESPACE, $session);
                //Установка объекта сессии в обработчик
                $this->setUserSession($session);
                //Обновляем дату последнего обращения к данным сессии игрока
                $session->setLastPingDate();
            }
        }
    }

    /**
     * Инициализация сессии игры
     */
    protected function _initGameSession()
    {
        //Проверка наличия в запросе идентификатора сессии игры
        $sid = $this->getRequest()->get('gamesession');
        if (null !== $sid) {
            //Создание объекта модели сессии игры
            $session = new App_Model_Session_Game();
            //Попытка получения данных сессии
            if ($session->find($sid)) {
                //Запись модели данных сессии в реестр
                Core_Session::getInstance()->set(Core_Session::GAME_NAMESPACE, $session);
                //Установка объекта сессии в обработчик
                $this->setGameSession($session);
                //Обновляем дату последнего обращения к данным сессии игры
                $session->setLastPingDate();
            } else {
                Zend_Registry::get('log')->debug('SESSION ' . $sid . ' NOT FOUND');
            }
        }
    }

    /**
     * Уничтожение обработанных объектов событий
     *
     * @throws Exception
     */
    protected function _destroyHandledEvents()
    {
        //Проверка наличия отработанных событий в игре
        $workedOut = array();
        foreach($this->getGameSession()->getData()->getEvents() as $event) {
            if ($event->isWorkedOut()) {
                //Добавляем в список завершенных событий
                $workedOut[] = $event;
            }
        }
        //Если есть, удаляем завершенные события
        if (count($workedOut)) {
            $this->getGameSession()->lockAndUpdate();
            try{
                foreach($workedOut as $event) {
                    //Получаем объект события из данных сессии, т.к. мы их обновили (lockAndUpdate)
                    //Удаляем событие
                    $this->getGameSession()->getData()->getEvent($event->getName())->destroy();
                }
            } catch (Exception $e) {
                $this->getGameSession()->unlock();
                throw $e;
            }
            $this->getGameSession()->saveAndUnlock();
        }
    }

    /**
     * Проверка старта новой партии в игре (изменение статуса с ENDGAME в PLAY. Вызывать только в пингах)
     *
     * @return void
     */
    protected function _checkForRestartGame()
    {
        //Проверка статуса игры, игра должна быть в состоянии завершения партии
        if ($this->getGameSession()->getData()->getStatus() != Core_Game_Abstract::STATUS_ENDGAME) {
            return;
        }
        //Проверка истечения 3 секунд с момента завершения партии
        if (time() - $this->getGameSession()->getData()->getLastUpdate() < 3) {
            return;
        }
        //Лочим данные сессии и полуучаем ее актуальные данные
        $this->getGameSession()->lockAndUpdate();
        //Проверка актуальности данных сессии
        if ($this->getRequest()->get('command') != $this->getGameSession()->getData()->getCommand()) {
            //Данные сессии были изменены, разблокируем данные сессии, ничего не делаем
            $this->getGameSession()->unlock();
            return;
        }
        //Инкремент порядкового номера изменения данных игры
        $this->getGameSession()->getData()->incCommand();
        //Генерация начального состояния игрового поля
        $this->getGameSession()->getData()->generate();
        //Изменения статуса игры на PLAY (начало следующей партии)
        $this->getGameSession()->getData()->setStatus(Core_Game_Abstract::STATUS_PLAY);
        //Изменение времени последнего изменения игры
        $this->getGameSession()->getData()->setLastUpdate();
        //Сохранение и разблокировка данных игровой сессии
        $this->getGameSession()->saveAndUnlock();
    }

    /**
     * Передача данных игрового стола в шаблон вида
     */
    protected function _assignViewGameData($assignChatData = true)
    {
        //Получаем данные игроков
        $userInfo = App_Model_Session_User::getUsersDataFromPlayersContainer(
            $this->getGameSession()->getData()->getPlayersContainer()
        );

        //Данные чата
        if ($assignChatData) {
            $chat = App_Model_Session_GameChat::chat($this->getGameSession()->getSid())->saveXml(
                $this->getUserSession()->getSid(),
                $this->getRequest()->get('chatId', 0)
            );
            //Передача данных чата
            $this->view->assign('chat', $chat);
        }

        //Передача данных в шаблон
        $this->view->assign(array(
            'userSess' => $this->getUserSession()->getSid(),
            'gameSess' => $this->getGameSession()->getSid(),
            'game' => $this->getGameSession()->getData(),
            'userInfo' => $userInfo,
            'vip' => intval($this->getGameSession()->isVip()),
            'sitdown' => intval($this->getGameSession()->canUserSitdown($this->getUserSession()->getSid()))
        ));
    }
}