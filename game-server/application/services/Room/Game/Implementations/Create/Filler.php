<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 01.03.12
 * Time: 11:22
 *
 * Реализация алгоритма создания игры Филлер
 */
class App_Service_Room_Game_Implementations_Create_Filler
    extends App_Service_Room_Game_Implementations_Create_Default
{

    /**
     * Создание объекта игры Филлер
     *
     * @return Core_Game_Abstract|Core_Game_Filler_Rectangle
     */
    public function createGameObject()
    {
        //Создание объекта игры
        $filler = new Core_Game_Filler_Rectangle();
        //Добавляем наблюдателей
        $filler->attach(new App_Service_Observers_Balance_Charge());
        $filler->attach(new App_Service_Observers_Balance_Deposit());
        $filler->attach(new App_Service_Observers_EndGame());
        $filler->attach(new App_Service_Observers_History());
        $filler->attach(new App_Service_Observers_Experience());
        $filler->attach(new App_Service_Observers_GameFinish());
        $filler->attach(new App_Service_Observers_GameDestroy());
        //Генерация игрового поля
        $filler->generate();

        //Установка параметров времени на ход и на партию (если у игрока куплена эта возможность CHESS_CLOCK)
        $timeout = $this->getParam('timeout', false);
        if (false !== $timeout && Core_Shop_Items::hasItem(Core_Shop_Items::CHESS_CLOCK)) {
            //Установка времени на ход
            if (isset($timeout['step'])) {
                $filler->setRunTimeout($timeout['step']);
            }
            //Установка времени на партию
            if (isset($timeout['game'])) {
                $filler->setGameTimeout($timeout['game']);
            }
        }

        //Установка суммы ставки
        if ($this->getParam('bet') > 0) {
            $filler->setBet($this->getParam('bet'));
        }

        //Установка количества партий
        $gamesCount = $this->getParam('match', 1);
        if ($gamesCount <= 0) {
            $gamesCount = 1;
        }
        $filler->setGamesCount($gamesCount);

        //Добавление пользователя в игру
        //TODO: учитывать правила первого хода
        $player = $filler->addPlayer(
            $this->getUserSession()->getSid(),
            $this->getUserSession()->getSocialUser()->getName(),
            Core_Game_Filler_Abstract::PLAYER_1
        );
        //Установка баланса игрока
        $player->setBalance($this->_getUserBalance());

        //Обновляем текущее состояние игры
        $filler->updateGameState();

        return $filler;
    }
}
