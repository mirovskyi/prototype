<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 14.06.12
 * Time: 12:44
 *
 * Класс описывающий методы сервиса для работы с опытом игроков
 */
class App_Server_Experience
{

    public function getExperience($idServiceUser, $nameService, $game)
    {
        //Объект модели работы с опытом пользователя
        $experience = new App_Service_Server_Experience($idServiceUser, $nameService, $game);
        //Возврашаем количество сыгранных партий
        return intval($experience->getUserExperience()->getNumber());
    }

    public function getWinCount($idServiceUser, $nameService, $game)
    {
        //Объект модели работы с опытом пользователя
        $experience = new App_Service_Server_Experience($idServiceUser, $nameService, $game);
        //Возврашаем количество выиграшей
        return intval($experience->getUserExperience()->getWin());
    }

    public function increment($idServiceUser, $nameService, $game)
    {
        //Объект модели работы с опытом пользователя
        $experience = new App_Service_Server_Experience($idServiceUser, $nameService, $game);
        //Увеличение количества сыгранных партий
        return $experience->increment();
    }

    public function win($idServiceUser, $nameService, $game)
    {
        //Объект модели работы с опытом пользователя
        $experience = new App_Service_Server_Experience($idServiceUser, $nameService, $game);
        //Увеличение количества выиграшей
        return $experience->win();
    }

}
