<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 22.05.12
 * Time: 17:02
 *
 * Обработчик события "сдаться" игры Дурак
 */
class App_Service_Handler_Event_Surrender_Durak implements App_Service_Handler_Event_Surrender_Interface
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

        //Действия при событии "сдаться" такие же, как при достижении игроком таймаута
        $player = $game->getData()->getPlayersContainer()->getPlayer($user->getSid());
        $game->getData()->getProcess()->handleTimeout($player);

        //Обновление состояния игры
        $game->getData()->updateGameState();
    }
}
