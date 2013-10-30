<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.07.12
 * Time: 14:11
 *
 * Помошник вида. Формирование блока данных победителей/проигравших
 */
class App_Service_View_Helper_Winblock extends Core_View_Helper_Abstract
{

    /**
     * Выполнение действий помошника вида
     *
     * @param Core_Game_Abstract $game
     *
     * @return string
     */
    public function winblock(Core_Game_Abstract $game)
    {
        $xml = new XMLWriter();
        $xml->openMemory();

        //Получение данных сессии текущего игрока
        $currentSession = Core_Session::getInstance()->get(Core_Session::USER_NAMESPACE);

        //Формрование данных для каждого игрока
        foreach($game->getPlayersContainer() as $player) {
            //Формирование идентификатора сессии игрока
            if ($currentSession instanceof App_Model_Session_User && $currentSession->getSid() == $player->getSid()) {
                $sid = $currentSession->getKey();
            } else {
                $sid = $player->getSid();
            }
            //Формирование данных результата игры для пользователя
            $xml->startElement('user');
            $xml->writeAttribute('sid', $sid);
            $xml->writeAttribute('status', $player->getStatus());
            $xml->writeAttribute('winamount', $player->getWinamount());
            //Если баланс игрока меньше минимальной ставки данной игры, скрываем кнопку "Другой оппонент"
            if ($player->getBalance() < $game->getStartBet()) { //TODO: получение миниальной ставки для стола
                $xml->writeAttribute('otheropponent', '0');
            } else {
                $xml->writeAttribute('otheropponent', '1');
            }
            $xml->endElement();
        }

        //Возвращаем список данных игроков
        return $xml->flush(false);
    }
}
