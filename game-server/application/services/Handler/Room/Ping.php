<?php

/**
 * Description of Ping
 *
 * @author aleksey
 */
class App_Service_Handler_Room_Ping extends App_Service_Handler_Abstract
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
     * Объект чата игрового зала
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
     * Получение данных чата игрового зала
     *
     * @return App_Model_Room_Chat
     */
    public function getRoomChat()
    {
        if (null === $this->_roomChat) {
            $this->_roomChat = new App_Model_Room_Chat($this->getGameName());
        }

        return $this->_roomChat;
    }
    
    /**
     * Обработка ping-запроса
     *
     * @param string|null $game [optional] Систеное имя игры
     * @return string
     */
    public function handle($game = null) 
    {
        if (null !== $game) {
            $this->setGameName($game);
        }

        //Возвращаем данные зала
        return $this->_getResponse();
    }
    
    /**
     * Формирование ответа сервера на ping-запрос
     *
     * @return string
     */
    protected function _getResponse()
    {
        //Получаем данные игрового зала
        $roomService = new App_Service_Room($this->getRoom());
        $games = $roomService->getGamesInfo($this->getUserSession());

        //Получение данных чата зала
        $chat = $this->getRoomChat()->getChat()->saveXml(
            $this->getUserSession()->getSid(),
            $this->getRequest()->get('chatId', 0)
        );

        //Передача данных игровой комнаты
        $this->view->assign(array(
            'games' => $games,
            'chat' => $chat
        ));
        //Возвращаем ответ
        return $this->view->render();
    }
    
}