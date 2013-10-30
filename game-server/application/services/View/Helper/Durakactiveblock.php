<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.07.12
 * Time: 17:33
 *
 * Помошник вида. Формирование блока статуса активности игроков
 */
class App_Service_View_Helper_Durakactiveblock extends Core_View_Helper_Abstract
{

    /**
     * Выполнение действий помошника вида. Формирование блока активности игроков
     *
     * @param string          $sid  Идентификатор сессии игрока, для которого необходимо сформировать данные
     * @param Core_Game_Durak $game Объект данных игры
     *
     * @return string
     */
    public function durakactiveblock($sid, Core_Game_Durak $game)
    {
        //Получаем объект текущего игрока
        $player = $game->getPlayersContainer()->getPlayer($sid);

        //Проверка возможности у игрока подкинуть карты
        $canThrowCard = $player ? $game->getProcess()->canAddCard($player) : false;
        //Проверка возможности отказаться подкинуть карту
        if (/*$canThrowCard && */$game->getProcess()->canRefuse($player)) {
            $canRefuse = true;
        } else {
            $canRefuse = false;
        }

        $xml = new XMLWriter();
        $xml->openMemory();
        //Получение данных сессии текущего игрока
        $currentSession = Core_Session::getInstance()->get(Core_Session::USER_NAMESPACE);
        //Формирование блока статуса активности игроков
        $xml->startElement('active');
        if ($player) {
            $xml->writeAttribute('throwin', (string)intval($canThrowCard));
            $xml->writeAttribute('refuse', (string)intval($canRefuse));
        }
        foreach($game->getPlayersContainer() as $player) {
            if ($player->isActive()) {
                //Формирование идентификатора сессии игрока
                if ($currentSession instanceof App_Model_Session_User && $currentSession->getSid() == $player->getSid()) {
                    $sid = $currentSession->getKey();
                } else {
                    $sid = $player->getSid();
                }
                $xml->writeElement('sid', $sid);
            }
        }
        $xml->endElement();
        //Формирование данных подкидывающего и отбивающегося игрока
        $xml->startElement('attacker');
        //Формирование идентификатора сессии атакующего
        $attackSid = $game->getPlayersContainer()->getAtackPlayer()->getSid();
        if ($currentSession instanceof App_Model_Session_User && $currentSession->getSid() == $attackSid) {
            $sid = $currentSession->getKey();
        } else {
            $sid = $attackSid;
        }
        $xml->writeAttribute('sid', $sid);
        $xml->endElement();
        $xml->startElement('defender');
        //Формирование идентификатора сессии отбивающегося
        $defenderSid = $game->getPlayersContainer()->getDefenderPlayer()->getSid();
        if ($currentSession instanceof App_Model_Session_User && $currentSession->getSid() == $defenderSid) {
            $sid = $currentSession->getKey();
        } else {
            $sid = $defenderSid;
        }
        $xml->writeAttribute('sid', $sid);
        $xml->writeAttribute('take', (string)intval($game->getProcess()->isLose()));
        $xml->endElement();

        //Отдаем блок активности игроков
        return $xml->flush(false);
    }

}
