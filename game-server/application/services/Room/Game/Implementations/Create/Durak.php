<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.04.12
 * Time: 10:05
 *
 * Реализация алгоритма создания игры Durak
 */
class App_Service_Room_Game_Implementations_Create_Durak
    extends App_Service_Room_Game_Implementations_Create_Default
{

    /**
     * Создание объекта игры Дурак
     *
     * @return Core_Game_Durak|Core_Game_Abstract
     */
    public function createGameObject()
    {
        //Создание объекта игры
        $durak = new Core_Game_Durak();
        //Добавляем наблюдателей
        $durak->attach(new App_Service_Observers_Balance_Charge());
        $durak->attach(new App_Service_Observers_Balance_Deposit());
        $durak->attach(new App_Service_Observers_EndGame());
        $durak->attach(new App_Service_Observers_History());
        $durak->attach(new App_Service_Observers_Experience());
        $durak->attach(new App_Service_Observers_GameFinish());
        $durak->attach(new App_Service_Observers_GameDestroy());

        //Инкремент порядкового номера обновления
        $durak->incCommand();

        //Установка параметров времени на ход и на партию (если у игрока куплена эта возможность CHESS_CLOCK)
        $timeout = $this->getParam('timeout', false);
        if (false !== $timeout && Core_Shop_Items::hasItem(Core_Shop_Items::CHESS_CLOCK)) {
            //Установка времени на ход
            if (isset($timeout['step'])) {
                $durak->setRunTimeout($timeout['step']);
            }
            //Установка времени на партию
            if (isset($timeout['game'])) {
                $durak->setGameTimeout($timeout['game']);
            }
        }

        //Установка суммы ставки
        if ($this->getParam('bet') > 0) {
            $durak->setBet($this->getParam('bet'));
        }

        //Установка максимального количества игроков за столом
        if (null !== $this->getParam('uc')) {
            $durak->setMaxPlayersCount($this->getParam('uc'));
        }

        //Проверка наличия флага игры в режиме матча
        $match = $this->getParam('match');
        if ($match > 1) {
            //Установка количества партий в матче
            $durak->setGamesCount($match);
        }

        //Добавление пользователя в игру
        //TODO: учитывать правила первого хода
        $player = $durak->addPlayer(
            $this->getUserSession()->getSid(),
            $this->getUserSession()->getSocialUser()->getName()
        );
        //Установка баланса игрока
        $player->setBalance($this->_getUserBalance());

        //Обновление времено изменения
        $durak->setLastUpdate();

        return $durak;
    }
}
