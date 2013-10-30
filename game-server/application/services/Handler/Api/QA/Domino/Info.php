<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 26.07.12
 * Time: 9:46
 *
 * Обработчик получения данных игры Домино (для тестов)
 */
class App_Service_Handler_Api_QA_Domino_Info extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    public function handle()
    {
        //Получение данных игры
        $game = $this->_getGame();
        //Формрование данных для передачи в шаблон ответа
        $params = array(
            'command' => $game->getCommand(),
            'status' => $game->getStatus(),
            'bet' => $game->getBet(),
            'reserve' => null,
            'series' => null,
        );
        //Формирование данных иигрового стола
        if ($game->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            $params['reserve'] = $game->getReserve()->__toString();
            $params['series'] = $game->getSeries()->__toString();
        }
        //Формирвоание данных игроков
        $players = array();
        foreach($game->getPlayersContainer() as $player) {
            $players[$player->getSid()] = array(
                'name' => $player->getName(),
                'time' => $player->getGametime(),
                'bones' => null
            );
            if ($game->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
                $players[$player->getSid()]['bones'] = $player->getBoneArray()->__toString();
            }
        }
        $params['players'] = $players;
        //Возвращаем ответ сервера
        $this->view->assign($params);
        return $this->view->render();
    }

    /**
     * Получение объекта игры
     *
     * @return Core_Game_Domino
     */
    private function _getGame()
    {
        return $this->getGameSession()->getData();
    }
}
