<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 13.03.12
 * Time: 13:37
 *
 * Класс наблюдателя завершения игры
 */
class App_Service_Observers_GameFinish implements SplObserver
{

    /**
     * Обработка изменения состояния игры
     *
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject)
    {
        $this->_handle($subject);
    }

    /**
     * Обработка события завершения игры
     *
     * @param Core_Game_Abstract $game
     * @return mixed
     */
    protected function _handle(Core_Game_Abstract $game)
    {
        //Проверка завершения игры
        if ($game->getStatus() != Core_Game_Abstract::STATUS_FINISH) {
            return;
        }

        //Проверка соответствия данной игры и игры из данных сессии
        $session = $this->_getGameSession($game->getId());
        if (!$session) {
            return;
        }

        //Блокировка данны игры
        $isLock = false;
        if (!$session->isLock(posix_getpid())) {
            $session->lockAndUpdate();
            $isLock = true;
        }

        //Изменяем статуc всех игроков в игре
        $this->_changePlayersStatus($session);

        //Если нет события закрытия игрового стола, необходима проверка возможности рестарта для каждого игрока
        if (!$game->hasEvent(App_Service_Events_Gameclose::name())) {
            //Проверка возможности рестарта игры у всех пользователей (проверка наличия необходимого баланса)
            foreach($game->getPlayersContainer() as $player) {
                //Получаем текущий баланс пользователя
                $balance = $player->getBalance();
                //Если баланса недостаточно для продолжения игры, создаем для игрока событие закрытия игрового стола
                if ($balance < $game->getStartBet()) {
                    $this->_createGameCloseEvent($game, $player);
                }
            }
        }

        //Разблокируем данные игры
        if ($isLock) {
            $session->saveAndUnlock();
        }

        //Удаление обработчика события завершения игры
        $game->detach($this);
    }

    /**
     * Изменение статуса всех пользователей в игре (установка вне игры)
     *
     * @param App_Model_Session_Game $session
     */
    protected function _changePlayersStatus(App_Model_Session_Game $session)
    {
        //Изменение статусов всех игроков
        foreach($session->getData()->getPlayersContainer() as $player) {
            $player->setPlay(false);
        }
    }

    /**
     * Создание события закрытия игрового стола для игрока
     *
     * @param Core_Game_Abstract $game Объект данных игры
     * @param string             $sid  Идентификатор сессии игрока
     */
    protected function _createGameCloseEvent(Core_Game_Abstract $game, $sid)
    {
        //Проверка наличия данного события в игре для пользователя
        if (!$game->hasEvent(App_Service_Events_UserGameclose::name($sid))) {
            //Создание события закрытия стола для пользователя
            $event = new App_Service_Events_UserGameclose($sid);
            //Добавление события в игру
            $game->addEvent($event);
        }
    }

    /**
     * Получение объекта данных игровой сессии
     *
     * @param string $sid Идентификатор сессии игры
     *
     * @return App_Model_Session_Game
     */
    private function _getGameSession($sid)
    {
        $session = Core_Session::getInstance()->get(Core_Session::GAME_NAMESPACE);
        if ($session != $sid) {
            $session = new App_Model_Session_Game();
            if (!$session->find($sid)) {
                return false;
            }
        }

        return $session;
    }
}
