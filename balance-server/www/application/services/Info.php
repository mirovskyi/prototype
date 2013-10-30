<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 17.02.12
 * Time: 17:03
 *
 * Модель получения данных игрового сервиса
 */
class App_Service_Info
{

    public function getGames()
    {
        //Получаем модель игры в БД
        $game = new App_Model_Game();

        //Формируем список игр
        $games = array();
        foreach($game->fetchAll() as $item) {
            $games[] = array(
                'name' => $item->getName(),
                'title' => $item->getTitle(),
                'url' => $item->getUrl()
            );
        }

        //Возвращаем список игр
        return $games;
    }

}
