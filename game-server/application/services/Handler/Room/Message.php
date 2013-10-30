<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 04.06.12
 * Time: 17:34
 *
 * Обработчик добавления сообщения чата игрового зала
 */
class App_Service_Handler_Room_Message extends App_Service_Handler_Abstract
{

    /**
     * Системное имя игры
     *
     * @var string
     */
    protected $_game;

    /**
     * Объект игрового зала
     *
     * @var App_Model_Room
     */
    protected $_room;

    /**
     * Объект данных чата игрового зала
     *
     * @var App_Model_Room_Chat
     */
    protected $_roomChat;


    /**
     * Установка системного имени игры
     *
     * @param string $game
     * @return App_Service_Handler_Room_Ping
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
     * Получение объекта данных чата игрового зала
     *
     * @param bool $init Флаг получения данных чата при инициализации объекта
     *
     * @return App_Model_Room_Chat
     */
    public function getRoomChat($init = true)
    {
        if (null === $this->_roomChat) {
            $this->_roomChat = new App_Model_Room_Chat($this->getRequest()->get('game'));
        }
        return $this->_roomChat;
    }

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @throws Exception
     * @return string
     */
    public function handle()
    {
        //Получение текста сообщения
        $message = $this->getRequest()->get('text');
        //Получение адресата сообщения
        $recipient = $this->getRequest()->get('recipient');
        //Добавление сообщекния в чат
        $this->getRoomChat(false)->lockAndUpdate();
        try {
            $this->getRoomChat()->getChat()->addMessage($message, $this->getUserSession()->getSid(), $recipient);
        } catch (Exception $e) {
            //Разблокируем данные чата
            $this->getRoomChat()->unlock();
            //Выбрасываем исключение
            throw $e;
        }
        //Сохраняем и разблокируем данные чата
        $this->getRoomChat()->saveAndUnlock();

        //Возвращаем ответ сервера
        return $this->_getResponse();
    }

    /**
     * Получение ответа сервера
     *
     * @return string
     */
    protected function _getResponse()
    {
        //Получаем данные игрового зала
        $roomService = new App_Service_Room($this->getRoom());
        $games = $roomService->getGamesInfo($this->getUserSession());

        //Полвение данных чата
        $chat = $this->getRoomChat()->getChat()->saveXml(
            $this->getUserSession()->getSid(),
            $this->getRequest()->get('chatId')
        );
        //Вывод шаблона ответа
        $this->view->assign(array(
            'games' => $games,
            'chat' => $chat
        ));
        return $this->view->render();
    }
}
