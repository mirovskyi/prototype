<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.06.12
 * Time: 10:06
 *
 * Реализация алгоритма создания игры Нарды
 */
class App_Service_Room_Game_Implementations_Create_Backgammon
    extends App_Service_Room_Game_Implementations_Create_Default
{

    /**
     * Создание объекта игры Домино
     *
     * @return Core_Game_Backgammon|Core_Game_Abstract
     */
    public function createGameObject()
    {
        //Создание объекта игры
        $backgammon = new Core_Game_Backgammon();
        //Добавляем наблюдателей
        $backgammon->attach(new App_Service_Observers_Balance_Charge());
        $backgammon->attach(new App_Service_Observers_Balance_Deposit());
        $backgammon->attach(new App_Service_Observers_EndGame());
        $backgammon->attach(new App_Service_Observers_History());
        $backgammon->attach(new App_Service_Observers_Experience());
        $backgammon->attach(new App_Service_Observers_GameFinish());
        $backgammon->attach(new App_Service_Observers_GameDestroy());

        //Инкремент порядкового номера обновления
        $backgammon->incCommand();

        //Установка параметров времени на ход и на партию (если у игрока куплена эта возможность CHESS_CLOCK)
        $timeout = $this->getParam('timeout', false);
        if (false !== $timeout && Core_Shop_Items::hasItem(Core_Shop_Items::CHESS_CLOCK)) {
            //Установка времени на ход
            if (isset($timeout['step'])) {
                $backgammon->setRunTimeout($timeout['step']);
            }
            //Установка времени на партию
            if (isset($timeout['game'])) {
                $backgammon->setGameTimeout($timeout['game']);
            }
        }

        //Установка суммы ставки
        if ($this->getParam('bet') > 0) {
            $backgammon->setBet($this->getParam('bet'));
        }

        //Установка количества партий
        $gamesCount = $this->getParam('match', 2);
        if ($gamesCount <= 0) {
            $gamesCount = 1;
        }
        $backgammon->setGamesCount($gamesCount);

        //Право первого хода
        $firstMove = $this->getParam('fm', 2); //2 - случайный выбор
        //Определение цвета шашек у создателя
        if ($firstMove == 0) {
            $color = Core_Game_Backgammon_Board::WHITE_PIECE;
        } elseif ($firstMove == 1) {
            $color = Core_Game_Backgammon_Board::BLACK_PIECE;
        } else {
            $color = null;
        }

        //Добавление пользователя в игру
        $player = $backgammon->addPlayer(
            $this->getUserSession()->getSid(),
            $this->getUserSession()->getSocialUser()->getName(),
            $color
        );
        //Установка баланса игрока
        $player->setBalance($this->_getUserBalance());

        //Обновление времено изменения
        $backgammon->setLastUpdate();

        return $backgammon;
    }

}
