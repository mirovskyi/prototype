<?php

/**
 * Description of Create
 *
 * @author aleksey
 */
class App_Service_Handler_Game_Create extends App_Service_Handler_Abstract
{

    /**
     * Получение имени игры
     *
     * @return string
     */
    public function getGameName()
    {
        return $this->getRequest()->get('game');
    }

    /**
     * Обработка запроса создания игры
     *
     * @param string|null $game [optional] Системное имя игры
     * @throws Core_Exception
     * @return string
     */
    public function handle($game = null)
    {
        //Имя игр
        if (null === $game) {
            $game = $this->getGameName();
        }

        //Объект игрового зала
        $room = new App_Model_Room($game);

        //Проверка наличия пользователя в игровом зале
        if (!$room->hasUser($this->getUserSession()->getSid())) {
            throw new Core_Exception('User session was not found in room', 301);
        }

        //Созание игрового стола
        $gameSession = $this->_createGame();

        //Добавление сессии игрового стола в зал и установка флага игры пользователя
        $room->lockAndUpdate();
        $room->addGame($gameSession->getSid());
        $room->setUserInGame($this->getUserSession()->getSid(), $gameSession->getSid());
        $room->saveAndUnlock();

        //Возвращаем ответ сервера
        return $this->_getResponse($gameSession);
    }

    /**
     * Добавление нового игрового стола в игровой зал
     *
     * @return App_Model_Session_Game
     * @throws Core_Exception
     */
    protected function _createGame()
    {
        //Параметры игрового стола
        $params = $this->getRequest()->get('gamedata');
        if (!$params) {
            $params = array();
        }
        //Создание игрового стола
        $gameCreate = App_Service_Room_Game_Create::factory(
            $this->getGameName(),
            $this->getUserSession(),
            $params
        );
        $game = $gameCreate->create();

        //Возвращаем созданный игровой стол
        return $game;
    }
    
    /**
     * Получение данных ответа
     *
     * @param App_Model_Session_Game $gameSession
     * @return string 
     */
    protected function _getResponse(App_Model_Session_Game $gameSession)
    {
        //Объект игры
        $game = $gameSession->getData();

        //Установка сессии игры в обработчик
        $this->setGameSession($gameSession);

        //Передача данных игрового стола в шаблон
        $this->_assignViewGameData();

        //Флаг открытия игрового стола
        $this->view->assign('isOpen', true);

        //Отдаем данные шаблона игры
        $template = $game->getName() . '/update';
        return $this->view->render($template);
    }
}