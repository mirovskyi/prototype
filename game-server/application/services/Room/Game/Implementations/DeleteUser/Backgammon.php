<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.06.12
 * Time: 11:33
 *
 * Реализация алгоритма добавления пользователя в игру Нарды
 */
class App_Service_Room_Game_Implementations_DeleteUser_Backgammon
    extends App_Service_Room_Game_Templates_DeleteUser
{

    /**
     * Удаление пользователя из игрового стола и игровой сессии
     *
     * @return void
     */
    public function deleteUserFromGame()
    {
        //Получаем идентификатор сессии удаляемого игрока
        $sid = $this->getUserSession()->getSid();

        //Проверка текущего статуса игры
        if ($this->_getGame()->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            //Получение данных игрока
            $player = $this->_getGame()->getPlayersContainer()->getPlayer($sid);
            //Получаем оппонента
            $this->_getGame()->getPlayersContainer()->getIterator()->setCurrentElement($player);
            $opponent = $this->_getGame()->getPlayersContainer()->getIterator()->nextElement();
            //Установка оппонента в качестве победителя
            $this->_getGame()->setWinner($opponent);
        }

        //Удаление игрока
        $this->_getGame()->getPlayersContainer()->deletePlayer($sid);
        //Если текущий статус игры PLAY - завершаем игру
        if ($this->_getGame()->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            //Изменяем статус игры на FINISH
            $this->_getGame()->setStatus(Core_Game_Abstract::STATUS_FINISH);
        }

        //Обновляем данные игры
        $this->_getGame()->updateGameState();
    }

    /**
     * Удаление пользователя из чата игры
     *
     * @return void
     */
    public function deleteUserFromChat()
    {
        //Объект чата
        $chat = $this->getChatSession()->getChat();
        //Удаление игрока из чата
        $chat->dellPlayer($this->getUserSession()->getSid());
    }

    /**
     * Получение объекта игры
     *
     * @return Core_Game_Backgammon
     */
    protected function _getGame()
    {
        return $this->getGameSession()->getData();
    }

}
