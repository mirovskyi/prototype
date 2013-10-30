<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.06.12
 * Time: 15:37
 *
 * Реализация алгоритма добавления пользователя в игру Домино
 */
class App_Service_Room_Game_Implementations_DeleteUser_Domino
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
        if ($this->_getDominoGame()->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            //Получение данных игрока
            $player = $this->_getDominoGame()->getPlayersContainer()->getPlayer($sid);
            //Обработка выхода игрока из игры
            $this->_getDominoGame()->finish($player);
        }

        //Удаление игрока
        $this->_getDominoGame()->getPlayersContainer()->deletePlayer($sid);
        //Если текущий статус игры PLAY - завершаем игру
        if ($this->_getDominoGame()->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            //Изменяем статус игры на FINISH
            $this->_getDominoGame()->setStatus(Core_Game_Abstract::STATUS_FINISH);
        }

        //Обновляем данные игры
        $this->_getDominoGame()->updateGameState();
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
     * @return Core_Game_Domino
     */
    protected function _getDominoGame()
    {
        return $this->getGameSession()->getData();
    }
}
