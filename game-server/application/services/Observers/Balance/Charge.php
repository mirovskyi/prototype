<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.03.12
 * Time: 11:47
 *
 * Класс наблюдателя за игрой. Списания средств с баланса игроков.
 */
class App_Service_Observers_Balance_Charge implements SplObserver
{

    /**
     * Список сумм списаных с игроков
     *
     * @var array
     */
    protected $_charges = array();


    /**
     * Получение списка списанных сумм с игроков
     *
     * @return array
     */
    public function getCharges()
    {
        return $this->_charges;
    }

    /**
     * Получение суммы списанной с игрока
     *
     * @param string $sid Идентификатор сессии пользователя
     * @return bool
     */
    public function getChargeInfo($sid)
    {
        if (isset($this->_charges[$sid])) {
            return $this->_charges[$sid];
        }

        return false;
    }

    /**
     * Получение суммы списания игрока
     *
     * @param string $sid Идентификатор сессии игрока
     * @return bool
     */
    public function getChargeAmount($sid)
    {
        if (isset($this->_charges[$sid])) {
            return $this->_charges[$sid][0];
        }

        return false;
    }

    /**
     * Получение идентификатора пользователя в соц. сети по идентификатору сессии игрока
     *
     * @param $sid
     * @return bool
     */
    public function getChargeUserId($sid)
    {
        if (isset($this->_charges[$sid])) {
            return $this->_charges[$sid][1];
        }

        return false;
    }

    /**
     * Получение системного имени соц. сети пользователя по идентификатору сессии игрока
     *
     * @param string $sid Идентификатор сессии игрока
     * @return bool
     */
    public function getChargeUserNetwork($sid)
    {
        if (isset($this->_charges[$sid])) {
            return $this->_charges[$sid][2];
        }

        return false;
    }

    /**
     * Обработка обновления данных игры
     *
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject)
    {
        //Проверка статуса игры
        if ($this->_checkStatus($subject)) {
            //Обработка изменения состояния игры
            $this->_handle($subject);
        }
    }

    /**
     * Проверка начала игры
     *
     * @param Core_Game_Abstract $game
     * @return bool
     */
    protected function _checkStatus(Core_Game_Abstract $game)
    {
        return $game->getStatus() == Core_Game_Abstract::STATUS_PLAY;
    }

    /**
     * Обработка изменения состояния игры
     *
     * @param Core_Game_Abstract $game
     */
    protected function _handle(Core_Game_Abstract $game)
    {
        //Текущая сумма ставки
        $bet = $game->getBet();

        //Проверка соответствия списанных средств с суммой ставки
        foreach($game->getPlayersContainer() as $player) {
            //Идентификатор сессии пользователя
            $sid = $player->getSid();
            //Проверка соответствия списанной суммы с суммой ставки
            if ($this->getChargeAmount($sid) != $bet) {
                //Списания средств c игрового счета пользователя
                $this->_charge($sid, $bet);
            }
        }
    }

    /**
     * Списания разницы текущей ставки и списанной суммы с баланса игрока
     *
     * @param string $sid
     * @param int $bet
     * @return void
     */
    private function _charge($sid, $bet)
    {
        //Получение сессии пользователя
        $session = $this->_getUserSession($sid);

        //Определение суммы списания
        $chargeAmount = $bet - $this->getChargeAmount($sid);
        if ($chargeAmount <= 0) {
            return;
        }

        //API сервиса балансов пользователей
        $api = new Core_Api_DataService_Balance();
        //Списание средств
        $result = $api->charge(
            $session->getSocialUser()->getId(),
            $session->getSocialUser()->getNetwork(),
            $chargeAmount
        );
        //Установка списанной суммы
        if ($result) {
            //Запись данных списания
            $this->_charges[$sid] = array(
                $bet,
                //Данные пользователя нужны для отката списания в зависших играх (есть вероятность потери сессии игрока на момент отката)
                $session->getSocialUser()->getId(),
                $session->getSocialUser()->getNetwork(),
            );
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
        if (!$session->find($sid)) {
            throw new Core_Exception('User session was not found', 103);
        }

        return $session;
    }
}
