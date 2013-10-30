<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 13.08.12
 * Time: 11:52
 *
 * Помошник вида. Данные блока остатка времени игроков.
 */
class App_Service_View_Helper_Timeout extends Core_View_Helper_Abstract
{

    public function timeout(Core_Game_Abstract $game)
    {
        $xml = new XMLWriter();
        $xml->openMemory();

        //Получение данных сессии текущего игрока
        $currentSession = Core_Session::getInstance()->get(Core_Session::USER_NAMESPACE);

        //Формирование данных по каждому игроку
        foreach($game->getPlayersContainer() as $player) {
            //Формирование идентификатора сессии игрока
            if ($currentSession instanceof App_Model_Session_User && $currentSession->getSid() == $player->getSid()) {
                $sid = $currentSession->getKey();
            } else {
                $sid = $player->getSid();
            }
            //Формирование данных таймера игрока
            $xml->startElement('user');
            $xml->writeAttribute('sid', $sid);
            //Добавление остатка времени на ход
            $xml->writeElement('step', $game->getPlayerRuntime($player));
            //Добавление остатка времени на партию
            $xml->writeElement('game', $game->getPlayerGametime($player));
            $xml->endElement();
        }

        //Отдаем блок данных остатка времени игроков
        return $xml->flush(false);
    }

}
