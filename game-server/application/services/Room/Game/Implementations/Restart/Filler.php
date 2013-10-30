<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 09.05.12
 * Time: 12:13
 *
 * Реализация рестарта игры Филлер
 */
class App_Service_Room_Game_Implementations_Restart_Filler
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

        //Проверка первого рестарта игры.
        //Если это первый игрок который рестартит игру, необходимо сгенерировть начальное состояние игрового стола
        $firstRestart = true;
        foreach($game->getPlayersContainer() as $player) {
            if ($player->isPlay()) {
                $firstRestart = false;
            }
        }
        if ($firstRestart) {
            //Генерация начального состояния игрового стола
            $game->generate();
        }

        //Изменение статуса игрока
        $player = $game->getPlayersContainer()->getPlayer($this->getUserSession()->getSid());
        $player->setPlay();
        //Обнуление набранных очков игрока и статуса
        $player->setPoints(0);
        $player->setStatus(Core_Game_Players_Player::STATUS_NONE);

        //Проверка возможности начать игру
        if ($game->canPlay()) {
            //Начало игры
            //TODO: установка активного игрока
            $game->getPlayersContainer()->setActive($this->getGameSession()->getCreatorSid());
            $game->setStatus(Core_Game_Abstract::STATUS_PLAY);
            //Обновление состояния игры
            $game->updateGameState();
        } else {
            //Ожидание оппонентов
            $game->setStatus(Core_Game_Abstract::STATUS_WAIT);
        }
    }

}
