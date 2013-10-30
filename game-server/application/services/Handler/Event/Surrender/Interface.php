<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 22.05.12
 * Time: 16:40
 *
 * Интерфейс обработчика события "сдаться"
 */
interface App_Service_Handler_Event_Surrender_Interface
{

    /**
     * Обработка события "сдаться" от игрока в игре
     * !!!Лочить сессии нет надобности
     *
     * @abstract
     * @param App_Model_Session_User $user Объект сессии пользователя, который хочет сдаться
     * @param App_Model_Session_Game $game Объект сессии игры
     * @throws Exception
     */
    public function surrender(App_Model_Session_User $user, App_Model_Session_Game $game);

}
