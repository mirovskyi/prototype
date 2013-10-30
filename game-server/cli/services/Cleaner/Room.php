<?php

 
class Cli_Service_Cleaner_Room
{

    /**
     * Пространство имен игровго зала
     *
     * @var string
     */
    protected $_namespace;

    /**
     * Объект игрового зала
     *
     * @var App_Model_Room
     */
    protected $_room;

    /**
     * Объект чата игрового зала
     *
     * @var App_Model_Room_Chat
     */
    protected $_roomChat;


    /**
     * __construct
     *
     * @param string|null $namespace
     */
    public function __construct($namespace = null)
    {
        $this->setNamespace($namespace);
    }

    /**
     * Установка пространство имен зала
     *
     * @param string $namespace
     * @return Cli_Service_Cleaner_Room
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
        return $this;
    }

    /**
     * Получение пространства имен зала
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Установка объекта игрового зала
     *
     * @param App_Model_Room $room
     * @return Cli_Service_Cleaner_Room
     */
    public function setRoom(App_Model_Room $room)
    {
        $this->_room = $room;
        return $this;
    }

    /**
     * Получение объекта игрового зала
     *
     * @return App_Model_Room
     */
    public function getRoom()
    {
        if (null === $this->_room) {
            $room = new App_Model_Room($this->getNamespace());
            $this->setRoom($room);
        }

        return $this->_room;
    }

    /**
     * Установка объекта чата игрового зала
     *
     * @param App_Model_Room_Chat $chat
     *
     * @return Cli_Service_Cleaner_Room
     */
    public function setRoomChat(App_Model_Room_Chat $chat)
    {
        $this->_roomChat = $chat;
        return $this;
    }

    /**
     * Получение обекта чата игрового зала
     *
     * @param bool $init Флаг получениея данных чата при инициализации объекта
     *
     * @return App_Model_Room_Chat
     */
    public function getRoomChat($init = true)
    {
        if (null === $this->_roomChat) {
            $chat = new App_Model_Room_Chat($this->getNamespace());
            $this->setRoomChat($chat);
        }

        return $this->_roomChat;
    }

    /**
     * Очистка зала от устаревших сессий
     *
     * @param int $userTimeout
     * @param int $gameTimeout
     * @return void
     */
    public function clear($userTimeout = 300, $gameTimeout = 60)
    {
        $this->clearUsers($userTimeout);
        $this->clearGames($gameTimeout);
    }

    /**
     * Очистка зала от устаревших сессий игроков
     *
     * @param int $timeout
     * @return void
     */
    public function clearUsers($timeout = 300)
    {
        //Список удаленных сессий
        $delSessions = array();

        //Проход по всем сессиям пользователей в зале
        foreach($this->getRoom()->getUsersSid() as $user) {
            //Получаем время последнего пинга от клиента
            $userSession = new App_Model_Session_User(array(
                'sid' => $user
            ));
            $lastPingTime = $userSession->getLastPingData();
            //Проверка истечения времени жизни сессии
            if (date('U') - $lastPingTime > $timeout) {
                //Получение данных сессии пользователя (для получения ключа записи)
                $userSession->find($user);
                //Удаляем сессию из хранилища
                $userSession->delete();
                //Очищаем данные о пинге
                $userSession->clearLastPingData();
                //Добавляем иднтификатр сессии в список удаленных
                $delSessions[] = $user;
            }
        }

        //Очищаем данные зала от удаленных сессий пользователей
        if (count($delSessions)) {

            //Удаление пользователей из зала
            $this->getRoom()->lockAndUpdate();
            foreach($delSessions as $sid) {
                $this->getRoom()->delUser($sid);
            }
            $this->getRoom()->saveAndUnlock();

            //Удаление пользователей из чата зала
            $this->getRoomChat(false)->lockAndUpdate();
            foreach($delSessions as $sid) {
                if ($this->getRoomChat()->getChat()->hasPlayer($sid)) {
                    $this->getRoomChat()->getChat()->dellPlayer($sid);
                }
            }
            $this->getRoomChat()->saveAndUnlock();

            //Логируем результат удаления
            $this->_log('Clean room from users: ' . implode(', ', $delSessions));
        } else {
            //Zend_Registry::get('log')->info('No user session for clear');
        }
    }

    /**
     * Очистка зала от устаревших игровых сессий
     *
     * @param int $timeout
     * @return void
     */
    public function clearGames($timeout = 60)
    {
        //Список удаленных сессий
        $delSessions = array();

        //Проход по всем сессиям пользователей в зале
        foreach($this->getRoom()->getGames() as $game) {
            //Получаем время последнего пинга игры
            $gameSession = new App_Model_Session_Game(array(
                'sid' => $game
            ));
            //Получение данных сессии
            $gameSession->find($game);
            //Время последнего пинга
            $lastPingTime = $gameSession->getLastPingData();

            //Проверка истечения времени жизни сессии
            if (date('U') - $lastPingTime > $timeout) {
                //Проверка статуса игры
                if ($gameSession->getData() && $gameSession->getData()->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
                    //Зависшая игра, возвращаем списаные деньги игрокам
                    foreach($gameSession->getData()->getObservers() as $observer) {
                        if ($observer instanceof App_Service_Observers_Balance_Charge) {
                            //Возврат списаных средств
                            $this->_refund($observer);
                        }
                    }
                }
                //Удаляем сессию из хранилища
                if ($gameSession->delete()) {
                    //Очищаем данные о пинге
                    $gameSession->clearLastPingData();
                }
                //Добавляем иднтификатр сессии в список удаленных
                $delSessions[] = $game;
            }
        }

        //Очищаем данные зала от удаленных игровых сессий
        if (count($delSessions)) {
            $this->getRoom()->lockAndUpdate();
            foreach($delSessions as $sid) {
                $this->getRoom()->delGame($sid);
            }
            $this->getRoom()->saveAndUnlock();

            //Логируем результат удаления
            $this->_log('Clean room from games: ' . implode(', ', $delSessions));
        } else {
            //$this->_log('No game session for clear');
        }
    }

    /**
     * Возврат средств игрокам
     *
     * @param App_Service_Observers_Balance_Charge $charge
     */
    private function _refund(App_Service_Observers_Balance_Charge $charge)
    {
        //Возвращаем списаные средства на счета пользователей
        foreach($charge->getCharges() as $sid => $info) {
            //Данные списания
            $id = $charge->getChargeUserId($sid);
            $network = $charge->getChargeUserNetwork($sid);
            $amount = $charge->getChargeAmount($sid);
            //Возвращение средств
            $api = new Core_Api_DataService_Balance();
            if ($api->deposit($id, $network, $amount)) {
                $this->_log('REFUND SUCCESS to user ' . $id . ':' . $network . ' amount:' . $amount);
            } else {
                $this->_log('REFUND FAILED to user ' . $id . ':' . $network . ' amount:' . $amount);
            }
        }
    }

    /**
     * Запись лога
     *
     * @param $message
     */
    private function _log($message)
    {
        if (Zend_Registry::getInstance()->isRegistered('log')) {
            Zend_Registry::get('log')->info($message);
        }
    }

}
