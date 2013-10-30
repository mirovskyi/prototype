<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 09.05.12
 * Time: 12:08
 *
 * Дэфолтная реализация рестарта игры
 */
class App_Service_Room_Game_Implementations_Restart_Default
    extends App_Service_Room_Game_Templates_Restart
{

    /**
     * Прикрепение наблюдателей игры
     *
     * @return void
     */
    public function reattachOvservers()
    {
        //Получение объекта игры
        $game = $this->getGameSession()->getData();
        //Добавление наблюдателей
        $game->attach(new App_Service_Observers_Balance_Charge());
        $game->attach(new App_Service_Observers_Balance_Deposit());
        $game->attach(new App_Service_Observers_History());
        $game->attach(new App_Service_Observers_Experience());
        $game->attach(new App_Service_Observers_GameFinish());
        $game->attach(new App_Service_Observers_GameDestroy());
    }

    /**
     * Инициализация игры
     *
     * @return void
     */
    public function reinitialize()
    {
        //Получение объекта игры
        $game = $this->getGameSession()->getData();

        //Изменение статуса игрока
        $player = $game->getPlayersContainer()->getPlayer($this->getUserSession()->getSid());
        $player->setPlay();
        //Обнуление набранных очков игрока и статуса
        $player->setPoints(0);
        $player->setStatus(Core_Game_Players_Player::STATUS_NONE);
        //Обнуление сыгранных матчей
        if (method_exists($game, 'setGamesPlay')) {
            $game->setGamesPlay(0);
        }

        //Проверка возможности начать игру
        if ($game->canPlay()) {
            //Начало игры
            $game->setStatus(Core_Game_Abstract::STATUS_PLAY);
            //Инкремент порядкового номера обновлени данных игры
            $game->incCommand();
            //Генерация начального состояния игрового стола
            $game->generate();
            //Обновление состояния игры
            $game->setLastUpdate();
        } else {
            //Ожидание оппонентов
            $game->setStatus(Core_Game_Abstract::STATUS_WAIT);
        }
    }
}
