<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.06.12
 * Time: 11:40
 *
 * Реализация рестарта игры Нарды
 */
class App_Service_Room_Game_Implementations_Restart_Backgammon
    extends App_Service_Room_Game_Implementations_Restart_Default
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

        //Проверка возможности начать игру
        if ($game->canPlay()) {
            //Начало игры
            $game->setStatus(Core_Game_Abstract::STATUS_PLAY);
            //Инкремент порядкового номера обновлени данных игры
            $game->incCommand();
            //Определение первого хода
            $this->_initActivePlayer();
            //Генерация начального состояния игрового стола
            $game->generate();
            //Обновление состояния игры
            $game->setLastUpdate();
        } else {
            //Ожидание оппонентов
            $game->setStatus(Core_Game_Abstract::STATUS_WAIT);
        }
    }

    /**
     * Установка активного игрока
     */
    private function _initActivePlayer()
    {
        //Объект данных игры
        $game = $this->getGameSession()->getData();
        //Получаем объект создателя игры
        $creatorSid = $this->getGameSession()->getCreatorSid();
        $creator = $game->getPlayersContainer()->getPlayer($creatorSid);
        //Правила первого хода
        $firstMove = $this->getGameSession()->getFirstMove();
        if ($firstMove !== null && $firstMove > 1) { //Создана быстрая игра или выбран случайный выбор (2)
            $firstMove = rand(0,1);
        }
        //Установка права первого хода
        if ($firstMove == 0) {
            //Установка первого права хода создателю игры
            $game->getPlayersContainer()->setActive($creator);
        } elseif ($firstMove == 1) {
            //Установка первого права хода оппоненту создателя игры
            $iterator = $game->getPlayersContainer()->getIterator();
            $iterator->setCurrentElement($creator);
            $opponent = $iterator->nextElement();
            $game->getPlayersContainer()->setActive($opponent);
        }

        //Установка белого цвета шашек игроку, который ходит первым, черные - второму игроку
        $game->getPlayersContainer()->getActivePlayer()->setId(Core_Game_Backgammon_Board::WHITE_PIECE);
        $iterator = $game->getPlayersContainer()->getIterator();
        $iterator->setCurrentElement($game->getPlayersContainer()->getActivePlayer());
        $iterator->nextElement()->setId(Core_Game_Backgammon_Board::BLACK_PIECE);
    }

}
