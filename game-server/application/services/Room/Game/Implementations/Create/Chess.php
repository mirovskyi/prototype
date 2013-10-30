<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 01.03.12
 * Time: 12:28
 *
 * Реализация алгоритма создания игры Шахматы
 */
class App_Service_Room_Game_Implementations_Create_Chess
    extends App_Service_Room_Game_Implementations_Create_Default
{

    /**
     * Создание объекта игры Шахматы
     *
     * @return Core_Game_Abstract|Core_Game_Chess
     */
    public function createGameObject()
    {
        //Создание объекта игры
        $chess = new Core_Game_Chess();
        //Добавляем наблюдателей
        $chess->attach(new App_Service_Observers_Balance_Charge());
        $chess->attach(new App_Service_Observers_Balance_Deposit());
        $chess->attach(new App_Service_Observers_EndGame());
        $chess->attach(new App_Service_Observers_History());
        $chess->attach(new App_Service_Observers_Experience());
        $chess->attach(new App_Service_Observers_GameFinish());
        $chess->attach(new App_Service_Observers_GameDestroy());
        //Генерация игрового поля
        $chess->generate();

        //Установка параметров времени на ход и на партию (если у игрока куплена эта возможность CHESS_CLOCK)
        $timeout = $this->getParam('timeout', false);
        if (false !== $timeout && Core_Shop_Items::hasItem(Core_Shop_Items::CHESS_CLOCK)) {
            //Установка времени на ход
            if (isset($timeout['step'])) {
                $chess->setRunTimeout($timeout['step']);
            }
            //Установка времени на партию
            if (isset($timeout['game'])) {
                $chess->setGameTimeout($timeout['game']);
            }
        }

        //Установка суммы ставки
        if ($this->getParam('bet') > 0) {
            $chess->setBet($this->getParam('bet'));
        }

        //Установка количества партий
        $gamesCount = $this->getParam('match', 1);
        if ($gamesCount <= 0) {
            $gamesCount = 1;
        }
        $chess->setGamesCount($gamesCount);

        //Опрделение первого хода в партии (опрделение цвета фигур создателя, белые ходят первыми)
        $firstMove = $this->getParam('fm', 0);
        if ($firstMove == 1) { //Ход противника - черные фигуры
            $color = Core_Game_Chess_Piece_Abstract::BLACK;
        } elseif ($firstMove == 2) { //Случайный выбор
            $color = rand(0, 1) > 0 ? Core_Game_Chess_Piece_Abstract::BLACK : Core_Game_Chess_Piece_Abstract::WHITE;
        } else { //Ход создателя игры - белые фигуры
            $color = Core_Game_Chess_Piece_Abstract::WHITE;
        }

        //Добавление пользователя в игру
        $player = $chess->addPlayer(
            $this->getUserSession()->getSid(),
            $this->getUserSession()->getSocialUser()->getName(),
            $color
        );
        //Установка баланса игрока
        $player->setBalance($this->_getUserBalance());

        //Обновляем текущее состояние игры
        $chess->updateGameState();

        return $chess;
    }

}
