<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 01.03.12
 * Time: 19:57
 *
 * Реализация алгоритма добавления пользователя в игру Дурак Переводной
 */
class App_Service_Room_Game_Implementations_DeleteUser_DurakTransfer
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
        if ($this->_getDurakGame()->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            //Получение данных игрока
            $player = $this->_getDurakGame()->getPlayersContainer()->getPlayer($sid);
            //Обработка выхода игрока из игры
            $this->_getDurakGame()->getProcess()->handleTimeout($player, true);
        }

        //Удаление игрока
        $this->_getDurakGame()->getPlayersContainer()->deletePlayer($sid);
        //Если текущий статус игры PLAY - завершаем игру
        if ($this->_getDurakGame()->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            //Изменяем статус игры на FINISH
            $this->_getDurakGame()->setStatus(Core_Game_Abstract::STATUS_FINISH);
        }

        //Обновляем данные игры
        $this->_getDurakGame()->updateGameState();
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
     * @return Core_Game_DurakTransfer
     */
    protected function _getDurakGame()
    {
        return $this->getGameSession()->getData();
    }
}
