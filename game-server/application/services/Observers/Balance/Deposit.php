<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.03.12
 * Time: 16:10
 *
 * Наблюдатель обновления данных игры. Пополнение счета победителя.
 */
class App_Service_Observers_Balance_Deposit implements SplObserver
{

    /**
     * Обновление данных игры
     *
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject)
    {
        if ($this->_handle($subject)) {
            //Удаляем наблюдателя
            $subject->detach($this);
        }
    }

    /**
     * Обработка обновления данных игры
     *
     * @param Core_Game_Abstract $game
     * @return bool
     */
    protected function _handle(Core_Game_Abstract $game)
    {
        //Проверка статуса игры
        if ($game->getStatus() == Core_Game_Abstract::STATUS_FINISH) {
            //Выплачиваем выигрышы пользователям
            foreach($game->getPlayersContainer() as $player) {
                //Проверка наличия выигрыша
                if ($player->getWinamount() > 0) {
                    //Выплата выигрыша
                    $this->_deposit($player->getSid(), $player->getWinamount());
                }
            }
            //Обновляем данные о балансах игроков за игровым столом
            $this->_updateBalanceInfo($game);
            //Слушатель отработал успешно
            return true;
        }

        return false;
    }

    /**
     * Пополнение счета игрока
     *
     * @param string $sid
     * @param int $amount
     * @return bool
     */
    private function _deposit($sid, $amount)
    {
        //Получение данных сессии пользователя
        $session = $this->_getUserSession($sid);
        //API срвиса балансов
        $api = new Core_Api_DataService_Balance();
        //Пополнение счета пользователя
        return $api->deposit(
            $session->getSocialUser()->getId(),
            $session->getSocialUser()->getNetwork(),
            $amount
        );
    }

    /**
     * Обновление данных о балансах игроков за игровым столом
     *
     * @param Core_Game_Abstract $game
     */
    private function _updateBalanceInfo(Core_Game_Abstract $game)
    {
        foreach($game->getPlayersContainer() as $player) {
            //Получение данных сессии пользователя
            $session = $this->_getUserSession($player->getSid());
            //Получение текущего баланса пользователя
            $api = new Core_Api_DataService_Balance();
            $balance = $api->getUserBalance(
                $session->getSocialUser()->getId(),
                $session->getSocialUser()->getNetwork()
            );
            //Обновление данных баланса игрока
            $player->setBalance($balance);
        }
    }

    /**
     * Получение объекта модели сессии пользователя
     *
     * @param string $sid
     * @return App_Model_Session_User
     * @throws Core_Exception
     */
    private function _getUserSession($sid)
    {
        //Попытка получения данных сессии из реестра
        if (Core_Session::getInstance()->has(Core_Session::USER_NAMESPACE)) {
            $session = Core_Session::getInstance()->get(Core_Session::USER_NAMESPACE);
            if ($session->getSid() == $sid) {
                return $session;
            }
        }

        //Получение данных сессии из хранилища
        $session = new App_Model_Session_User();
        $session->find($sid);
        if ($session->getSid() == null) {
            throw new Core_Exception('User session was not found', 103);
        }

        return $session;
    }

}
