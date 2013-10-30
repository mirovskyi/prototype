<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 18.06.12
 * Time: 11:27
 *
 * Обработчик события "сдаться" игры Домино
 */
class App_Service_Handler_Event_Surrender_Domino implements App_Service_Handler_Event_Surrender_Interface
{

    /**
     * Обработка события "сдаться" от игрока в игре
     * !!!Лочить сессии нет надобности
     *
     * @param App_Model_Session_User $user Объект сессии пользователя, который хочет сдаться
     * @param App_Model_Session_Game $game Объект сессии игры
     *
     * @throws Core_Exception
     * @return void
     */
    public function surrender(App_Model_Session_User $user, App_Model_Session_Game $game)
    {
        //Проверка текущего статуса игры
        if ($this->_getDomino($game)->getStatus() != Core_Game_Abstract::STATUS_PLAY) {
            throw new Core_Exception('Failed to surrender. Game does not play.', 211, Core_Exception::USER);
        }

        //Получение объекта сдавшегося игрока
        $surrenderPlayer = $this->_getDomino($game)->getPlayersContainer()->getPlayer($user->getSid());
        //Установка проигравшего игрока
        $this->_getDomino($game)->finish($surrenderPlayer);

        //Завершение игры
        $this->_getDomino($game)->setStatus(Core_Game_Abstract::STATUS_FINISH);

        //Обновление состояния игры
        $this->_getDomino($game)->updateGameState();
    }

    /**
     * Получение объекта игры Домино из данных игровой сессии
     *
     * @param App_Model_Session_Game $game
     *
     * @return Core_Game_Domino
     */
    private function _getDomino(App_Model_Session_Game $game)
    {
        return $game->getData();
    }
}
