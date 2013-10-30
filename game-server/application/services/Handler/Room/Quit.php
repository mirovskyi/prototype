<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 26.03.12
 * Time: 10:28
 *
 * Обработчик запроса выхода из игрового зала
 */
class App_Service_Handler_Room_Quit extends App_Service_Handler_Abstract
{

    /**
     * Объект модели игрового зала
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
     * Получение имени игры
     *
     * @return string
     */
    public function getGameName()
    {
        return $this->getRequest()->get('game');
    }

    /**
     * Получение объекта модели игрового зала
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
     * Получение объекта модели чата игрового зала
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
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    public function handle()
    {
        //Проверка наличия сессии игры (приходит в случае выхода в лобби из игрового стола)
        if ($this->hasGameSession()) {
            $game = $this->getGameSession();
            //Удаление пользователя из игровой сессии
            $delete = App_Service_Room_Game_DeleteUser::factory($this->getGameName(), $game, $this->getUserSession());
            $delete->delete();
        }

        //Удаление идентификатора сессии пользователя из данных игрового зала
        $this->getRoom()->lockAndUpdate();
        $this->getRoom()->delUser($this->getUserSession()->getSid());
        $this->getRoom()->saveAndUnlock();

        //Проверка наличия пользователя в чате игрового зала
        if ($this->getRoomChat()->getChat()->hasPlayer($this->getUserSession()->getSid())) {
            //Удаление пользователя из чата
            $this->getRoomChat()->lockAndUpdate();
            $this->getRoomChat()->getChat()->dellPlayer($this->getUserSession()->getSid());
            $this->getRoomChat()->saveAndUnlock();
        }

        //Удаление сессии
        $this->getUserSession()->delete();

        //Проверка необходимости передачи данных игровых серверов (данных лобби)
        if ($this->getRequest()->get('lobbyInfo')) {
            $this->view->assign('games', $this->_getGamesInfo());
        }
        //Возвращаем ответ сервера
        return $this->view->render();
    }

    /**
     * Получение данных игровых серверов
     *
     * @return array
     */
    private function _getGamesInfo()
    {
        $info = new Core_Api_DataService_Info();
        return $info->getGames();
    }

}
