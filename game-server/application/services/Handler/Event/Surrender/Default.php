<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 22.05.12
 * Time: 16:58
 *
 * Обработчик события "сдаться" по умолчанию
 */
class App_Service_Handler_Event_Surrender_Default implements App_Service_Handler_Event_Surrender_Interface
{

    /**
     * Обработка события "сдаться" от игрока в игре
     * !!!Лочить сессии нет надобности
     *
     * @param App_Model_Session_User $user Объект сессии пользователя, который хочет сдаться
     * @param App_Model_Session_Game $game Объект сессии игры
     *
     * @throws Core_Exception
     */
    public function surrender(App_Model_Session_User $user, App_Model_Session_Game $game)
    {
        //Проверка текущего статуса игры
        if ($game->getData()->getStatus() != Core_Game_Abstract::STATUS_PLAY) {
            throw new Core_Exception('Failed to surrender. Game does not play.', 211, Core_Exception::USER);
        }

        //Получение оппонента
        $iterator = $game->getData()->getPlayersContainer()->getIterator();
        $iterator->setCurrentElement($user->getSid());
        $opponent = $iterator->nextElement();

        //Установка оппонента как победителя
        $game->getData()->setWinner($opponent);

        //Завершение игры
        $game->getData()->setStatus(Core_Game_Abstract::STATUS_FINISH);

        //Обновление состояния игры
        $game->getData()->updateGameState();
    }
}
