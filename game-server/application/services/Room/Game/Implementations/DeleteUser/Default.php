<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 26.03.12
 * Time: 10:36
 *
 * Дэфолтная реализация удаления пользователя из игры
 */
class App_Service_Room_Game_Implementations_DeleteUser_Default
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
        if ($this->getGameSession()->getData()->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            //Установка оппонента игрока как победителя в игре
            $iterator = $this->getGameSession()->getData()->getPlayersContainer()->getIterator();
            $iterator->setCurrentElement($sid);
            $opponent = $iterator->nextElement();
            //Установка побудителя
            $this->getGameSession()->getData()->setWinner($opponent);
        }

        //Удаление игрока
        $this->getGameSession()->getData()->getPlayersContainer()->deletePlayer($sid);
        //Если текущий статус игры PLAY - завершаем игру
        if ($this->getGameSession()->getData()->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            //Изменяем статус игры на FINISH
            $this->getGameSession()->getData()->setStatus(Core_Game_Abstract::STATUS_FINISH);
        }

        //Обновляем данные игры
        $this->getGameSession()->getData()->updateGameState();
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

}
