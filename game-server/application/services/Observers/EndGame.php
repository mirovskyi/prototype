<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 31.10.12
 * Time: 10:35
 *
 * Слушатель события завершения партии в матче и начала новой
 */
class App_Service_Observers_EndGame implements SplObserver
{

    /**
     * Обработка изменения состояния игры
     *
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject)
    {
        $this->_handle($subject);
    }

    /**
     * Обработка события завершения партии в матче (но не всего матча)
     *
     * @param Core_Game_Abstract $game
     */
    private function _handle(Core_Game_Abstract $game)
    {
        //Проверка завершения партии
        if ($game->getStatus() != Core_Game_Abstract::STATUS_ENDGAME) {
            //Нет события окончания партии
            return;
        }

        //Очистка данных о предложении ничьи в партии
        App_Service_Handler_Event_Draw::clear($game->getId());
    }

    /**
     * Получение объекта текущей сессии игры
     *
     * @return App_Model_Session_Game|null
     */
    private function _getGameSession()
    {
        return Core_Session::getInstance()->get(Core_Session::GAME_NAMESPACE);
    }
}
