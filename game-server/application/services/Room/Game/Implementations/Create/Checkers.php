<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 01.03.12
 * Time: 12:24
 *
 * Реализация алгоритма создания игры Шашки
 */
class App_Service_Room_Game_Implementations_Create_Checkers
    extends App_Service_Room_Game_Implementations_Create_Default
{

    /**
     * Создание объекта игры Шашки
     *
     * @return Core_Game_Abstract|Core_Game_Checkers
     */
    public function createGameObject()
    {
        //Создание объекта игры
        $checkers = new Core_Game_Checkers();
        //Добавляем наблюдателей
        $checkers->attach(new App_Service_Observers_Balance_Charge());
        $checkers->attach(new App_Service_Observers_Balance_Deposit());
        $checkers->attach(new App_Service_Observers_EndGame());
        $checkers->attach(new App_Service_Observers_History());
        $checkers->attach(new App_Service_Observers_Experience());
        $checkers->attach(new App_Service_Observers_GameFinish());
        $checkers->attach(new App_Service_Observers_GameDestroy());
        //Генерация игрового поля
        $checkers->generate();

        //Установка параметров времени на ход и на партию (если у игрока куплена эта возможность CHESS_CLOCK)
        $timeout = $this->getParam('timeout', false);
        if (false !== $timeout && Core_Shop_Items::hasItem(Core_Shop_Items::CHESS_CLOCK)) {
            //Установка времени на ход
            if (isset($timeout['step'])) {
                $checkers->setRunTimeout($timeout['step']);
            }
            //Установка времени на партию
            if (isset($timeout['game'])) {
                $checkers->setGameTimeout($timeout['game']);
            }
        }

        //Установка суммы ставки
        if ($this->getParam('bet') > 0) {
            $checkers->setBet($this->getParam('bet'));
        }

        //Установка количества партий
        $gamesCount = $this->getParam('match', 1);
        if ($gamesCount <= 0) {
            $gamesCount = 1;
        }
        $checkers->setGamesCount($gamesCount);

        //Опрделение первого хода в партии (опрделение цвета шашек создателя, белые ходят первыми)
        $firstMove = $this->getParam('fm', 0);
        if ($firstMove == 1) { //Ход противника - черные шашки
            $color = Core_Game_Checkers_Piece::BLACK;
        } elseif ($firstMove == 2) { //Случайный выбор
            $color = rand(0, 1) > 0 ? Core_Game_Checkers_Piece::BLACK : Core_Game_Checkers_Piece::WHITE;
        } else { //Ход создателя игры - белые шашки
            $color = Core_Game_Checkers_Piece::WHITE;
        }

        //Добавление пользователя в игру
        $player = $checkers->addPlayer(
            $this->getUserSession()->getSid(),
            $this->getUserSession()->getSocialUser()->getName(),
            $color
        );
        //Установка баланса игрока
        $player->setBalance($this->_getUserBalance());

        //Обновляем текущее состояние игры
        $checkers->updateGameState();

        return $checkers;
    }

}
