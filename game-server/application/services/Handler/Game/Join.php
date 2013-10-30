<?php

/**
 * Description of Join
 *
 * @author aleksey
 */
class App_Service_Handler_Game_Join extends App_Service_Handler_Abstract 
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
     * Обработка запроса добавления пользователя в игру
     *
     * @throws Core_Exception
     * @return string
     */
    public function handle()
    {
        //Объект игрового зала
        $room = new App_Model_Room($this->getGameName());

        //Проверка наличия игрового стола в игровом зале
        if (!$room->hasGame($this->getGameSession()->getSid())) {
            throw new Core_Exception('Game session was not found in room', 302);
        }

        //Проверяем не закрыт ли игровой стол (наличие события закрытия игрового стола, это когда из-за стола встает создатель)
        $gameCloseEvent = App_Service_Events_Gameclose::name();
        if ($this->getGameSession()->getData()->hasEvent($gameCloseEvent)) {
            throw new Core_Exception('Game session has closed', 308);
        }

        //Проверка наличия пользователя в игровом зале
        if (!$room->hasUser($this->getUserSession()->getSid())) {
            throw new Core_Exception('User session was not found in room', 301);
        }

        //Добавляем игрока за игровой стол
        $this->_joinUser();

        //Установка флага игры пользователя за игровым столом
        $room->lockAndUpdate();
        $room->setUserInGame($this->getUserSession()->getSid(), $this->getGameSession()->getSid());
        $room->saveAndUnlock();

        //Возвращаем ответ сервера
        return $this->_getResponse();
    }

    /**
     * Добавление пользователя за игровой стол
     *
     * @return void
     */
    protected function _joinUser()
    {
        //Получаем позициию за игровым столом, куда хочет сесть игрок
        $position = $this->getRequest()->get('position', null);

        //Добавление пользователя за игровой стол
        $joinUser = App_Service_Room_Game_Join::factory(
            $this->getGameName(),
            $this->getGameSession(),
            $this->getUserSession()
        );
        $joinUser->join($position);
    }
    
    /**
     * Получение данных ответа на запрос
     *
     * @return string 
     */
    protected function _getResponse()
    {
        //Передача данных игрового стола в шаблон
        $this->_assignViewGameData();

        //Флаг открытия игрового стола
        $this->view->assign('isOpen', true);
        
        //Указываем путь к шаблону игры
        $template = $this->getGameSession()->getData()->getName() . '/update';

        return $this->view->render($template);
    }
    
}