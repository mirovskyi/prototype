<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 06.09.12
 * Time: 15:32
 *
 * Помошник вида. Формирование блока статуса игры
 */
class App_Service_View_Helper_Gamepoints extends Core_View_Helper_Abstract
{

    /**
     * Выполнение действий помошника вида
     *
     * @param Core_Game_Abstract $game   Объект данных игры
     * @param string            $userSid Идентификатор сессии игрока
     *
     * @return string
     */
    public function gamepoints(Core_Game_Abstract $game, $userSid)
    {
        //Проверка матчевой игры
        if (!$game->isMatch()) {
            //Данные об очках не нужны
            return '';
        }

        $points = $this->_getPoints($game, $userSid);

        //Формирование блока данных об очках
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->writeElement('points', $points);

        //Возвращаем данные статуса игры
        return $xml->flush(false);
    }

    /**
     * Получение количества очков игрока
     *
     * @param Core_Game_Abstract $game    Объект игры
     * @param string             $userSid Идентификатор сессии игрока
     *
     * @return int|string
     *
     */
    private function _getPoints(Core_Game_Abstract $game, $userSid)
    {
        //Получаем данные игрока
        $player = $game->getPlayersContainer()->getPlayer($userSid);
        //Проверка наличия игрока
        if (!$player) {
            return '';
        }

        //В зависимости от игры, отдаем необходимое количество очков
        if ($game instanceof Core_Game_Domino) {
            return $this->_getDominoPoints($player);
        } else {
            return $player->getPoints();
        }
    }

    /**
     * Получение количества очков игрока в Домино "Козел"
     *
     * @param Core_Game_Domino_Players_Player $player
     *
     * @return int|string
     */
    private function _getDominoPoints(Core_Game_Domino_Players_Player $player)
    {
        if ($player->getRememberPoints() > 0) {
            return '+' . $player->getRememberPoints();
        } else {
            return $player->getPoints();
        }
    }
}
