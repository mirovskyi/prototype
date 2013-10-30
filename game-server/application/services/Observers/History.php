<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.05.12
 * Time: 18:55
 *
 * Класс наблюдателя изменений состояния игры и записи в историю
 */
class App_Service_Observers_History implements SplObserver
{

    /**
     * Обработка обновления наблюдаемого объекта
     *
     * @param SplSubject $subject
     * @return void
     */
    public function update(SplSubject $subject)
    {
        $this->_handle($subject);
    }

    /**
     * Обработчик наблюдателя
     *
     * @param Core_Game_Abstract $game
     * @return void
     */
    private function _handle(Core_Game_Abstract $game)
    {
        //Проверка статуса игры
        $status = $game->getStatus();
        if ($status == Core_Game_Abstract::STATUS_WAIT) {
            return;
        }
        //Получение сессии игры
        $gameSess = $this->_getGameSession($game->getId());
        if (!$gameSess) {
            return;
        }

        //Получение объекта записи истории игр
        $history = Core_Game_History::getInstance();

        //Проверка наличия купленной услуги записи истории у игроков
        foreach($game->getPlayersContainer() as $player) {
            $userSess = $this->_getUserSession($player->getSid());
            if (!$userSess) {
                continue;
            }

            //Получаем данные пользователя из соц. сети
            $idUser = $userSess->getSocialUser()->getId();
            $network =  $userSess->getSocialUser()->getNetwork();

            //Проверка наличия у пользователя купленной услуги истории игр
            if (!$history->isAllowHistory($idUser, $network)) { //TODO: закоментить после тестов
            //if ($userSess->hasItem(Core_Shop_Items::GAME_HISTORY)) {
                continue;
            }

            //Запись в историю
            $history->addHitory($idUser, $network, $gameSess->getSid(), $game);

            //Если игра завершена сохраняем данные истории игры
            if ($status == Core_Game_Abstract::STATUS_FINISH) {
                //Сохранение данных истории в базу
                $history->saveHistory($idUser, $network, $gameSess->getSid());
            }
        }

        //Если игра окончена, удаляем слушателя
        if ($status == Core_Game_Abstract::STATUS_FINISH) {
            //Очистка кэша истории
            $history->clearHistoryCache($gameSess->getSid());
            //Удаление слушетеля
            $gameSess->getData()->detach($this);
        }
    }

    /**
     * Получение объекта сессии игрока
     *
     * @param string $sid Идентификатор сессии пользователя
     * @return App_Model_Session_User
     */
    private function _getUserSession($sid)
    {
        $session = new App_Model_Session_User();
        if ($session->find($sid)) {
            return $session;
        }

        return false;
    }

    /**
     * Получение объекта сессии игры
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
