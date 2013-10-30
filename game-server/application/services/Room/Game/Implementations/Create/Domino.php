<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.06.12
 * Time: 15:33
 *
 * Реализация алгоритма создания игры Domino
 */
class App_Service_Room_Game_Implementations_Create_Domino
    extends App_Service_Room_Game_Implementations_Create_Default
{

    /**
     * Создание объекта игры Домино
     *
     * @return Core_Game_Domino|Core_Game_Abstract
     */
    public function createGameObject()
    {
        //Создание объекта игры
        $domino = new Core_Game_Domino();
        //Добавляем наблюдателей
        $domino->attach(new App_Service_Observers_Balance_Charge());
        $domino->attach(new App_Service_Observers_Balance_Deposit());
        $domino->attach(new App_Service_Observers_EndGame());
        $domino->attach(new App_Service_Observers_History());
        $domino->attach(new App_Service_Observers_Experience());
        $domino->attach(new App_Service_Observers_GameFinish());
        $domino->attach(new App_Service_Observers_GameDestroy());

        //Инкремент порядкового номера обновления
        $domino->incCommand();

        //Установка параметров времени на ход и на партию (если у игрока куплена эта возможность CHESS_CLOCK)
        $timeout = $this->getParam('timeout', false);
        if (false !== $timeout && Core_Shop_Items::hasItem(Core_Shop_Items::CHESS_CLOCK)) {
            //Установка времени на ход
            if (isset($timeout['step'])) {
                $domino->setRunTimeout($timeout['step']);
            }
            //Установка времени на партию
            if (isset($timeout['game'])) {
                $domino->setGameTimeout($timeout['game']);
            }
        }

        //Установка суммы ставки
        if ($this->getParam('bet') > 0) {
            $domino->setBet($this->getParam('bet'));
        }

        //Установка максимального количества игроков за столом
        if (null !== $this->getParam('uc')) {
            $domino->setMaxPlayersCount($this->getParam('uc'));
        }

        //Проверка наличия флага игры в режиме матча
        if (null != $this->getParam('points')) {
            //Установка матчевого режима игры
            $domino->setMaxPoints($this->getParam('points'));
        }

        //Добавление пользователя в игру
        //TODO: учитывать правила первого хода
        $player = $domino->addPlayer(
            $this->getUserSession()->getSid(),
            $this->getUserSession()->getSocialUser()->getName()
        );
        //Установка баланса игрока
        $player->setBalance($this->_getUserBalance());

        //Обновление времено изменения
        $domino->setLastUpdate();

        return $domino;
    }

}
