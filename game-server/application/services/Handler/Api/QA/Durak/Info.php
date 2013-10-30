<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 10.07.12
 * Time: 11:36
 * Обработчик получения данных игры Дурак (для тестов)
 */
class App_Service_Handler_Api_QA_Durak_Info extends App_Service_Handler_Abstract
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
            'pack' => null,
            'trump' => null,
            'pulldown' => null
        );
        //Формирование данных иигрового стола
        if ($game->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            $params['pack'] = $game->getPack()->__toString();
            $params['trump'] = $game->getPack()->getTrump();
            $params['pulldown'] = $game->getPulldown()->__toString();
        }
        //Формирвоание данных игроков
        $players = array();
        foreach($game->getPlayersContainer() as $player) {
            $players[$player->getSid()] = array(
                'name' => $player->getName(),
                'time' => $player->getGametime(),
                'cards' => null
            );
            if ($game->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
                $players[$player->getSid()]['cards'] = $player->getCardArray()->__toString();
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
     * @return Core_Game_Durak
     */
    private function _getGame()
    {
        return $this->getGameSession()->getData();
    }
}
