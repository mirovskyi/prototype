<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 08.03.12
 * Time: 11:55
 *
 * Обработчик создания/подтверждения события увеличения ставки в игре
 */
class App_Service_Handler_Event_Bet extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса
     *
     * @throws Core_Exception
     * @return string
     */
    public function handle()
    {
        //Запрос подтверждения/отказа от события увеличения ставки
        if (null !== $this->getRequest()->get('confirm', null)) {
            $this->_handleConfirm();
        }
        //Запрос создания события увеличения ставки
        elseif ($this->getRequest()->get('bet')) {
            $this->_handleBet();
        }
        else {
            throw new Core_Exception('Invalid request params', 637, Core_Exception::USER);
        }

        //Возвращаем ответ сервера
        return $this->view->render();
    }

    /**
     * Обработка запроса создания события увеличения ставки
     *
     * @throws Core_Exception|Exception
     */
    protected function _handleBet()
    {
        //Сумма предложенной ставки
        $betAmount = $this->getRequest()->get('bet');

        //Проверка суммы предложенной ставки
        if ($betAmount <= $this->getGameSession()->getData()->getBet()) {
            throw new Core_Exception('Amount of the proposed bet must exceed the current', 1510, Core_Exception::USER);
        }

        //Проверяем возможность увеличить сумму ставки у всех игроков
        if (!$this->_canPlayersIncreaseBetAmount($betAmount)) {
            throw new Core_Exception('The amount of the increased bet can not exceed the minimum balance of players',
                                     1511, Core_Exception::USER);
        }

        //Получаем актуальные данные игровой сессии и блокируем ее
        $this->getGameSession()->lockAndUpdate();

        try {
            //Данные игры
            $game = $this->getGameSession()->getData();

            //Создание события увеличения ставки
            $event = new App_Service_Events_Bet($betAmount);
            //Добавляем событие в игру
            $game->addEvent($event);
            //Обработка события
            $game->handleEvent($event->getName());
        } catch (Exception $e) {
            //Разблокируем данные сессии игры
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохраняем данные сессии игры
        $this->getGameSession()->saveAndUnlock();
    }

    /**
     * Обработка запроса подтверждения/отмены события увеличения ставки
     *
     * @throws Core_Exception|Exception
     */
    protected function _handleConfirm()
    {
        //Имя события
        $eventName = App_Service_Events_Bet::name($this->getRequest()->get('bet'));

        //Проверка наличия события
        if (!$this->getGameSession()->getData()->hasEvent($eventName)) {
            throw new Core_Exception('Event does not exists', 1501, Core_Exception::USER);
        }

        //Получаем актуальные данные игровой сессии и блокируем ее
        $this->getGameSession()->lockAndUpdate();

        //Обработка события
        try {
            //Проверка подтверждения события
            if (true == $this->getRequest()->get('confirm')) {
                //Пользователь согласился. Обработка события
                $this->getGameSession()->getData()->handleEvent($eventName);
            } else {
                //Пользователь отказался.Получаем объект события
                $event = $this->getGameSession()->getData()->getEvent($eventName);
                //Добавляем пользователя в список оповещенных
                $event->notifyPlayer($this->getUserSession()->getSid());
                //Завершение события (генерация события отмены поднятия ставки)
                $event->destroy();
            }
        } catch (Exception $e) {
            //Разблокировка данных сессии игры
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохраняем данные сессии игры
        $this->getGameSession()->saveAndUnlock();
    }

    /**
     * Получение ответа сервера
     *
     * @return string
     */
    protected function _getResponse()
    {
        //Получаем имя игры
        $gameName = $this->getGameSession()->getName();
        //Установка пути к шаблону ответа
        $this->view->setTemplate($gameName . '/update');

        //Получаем данные игроков
        $userInfo = App_Model_Session_User::getUsersDataFromPlayersContainer(
            $this->getGameSession()->getData()->getPlayersContainer()
        );

        //Передача данных в шаблон
        $this->view->assign(array(
            'userSess' => $this->getUserSession()->getSid(),
            'gameSess' => $this->getGameSession()->getSid(),
            'game' => $this->getGameSession()->getData(),
            'userInfo' => $userInfo
        ));
        //Формирование ответа
        return $this->view->render();
    }

    /**
     * Проверка возможности у игроков увеличить ставку в игре (проверка балансов игроков)
     *
     * @param int $amount Сумма увеличенной ставки
     * @return bool
     * @throws Core_Exception
     */
    private function _canPlayersIncreaseBetAmount($amount)
    {
        //Проверка текущих балансов каждого пользователя
        foreach($this->getGameSession()->getData()->getPlayersContainer() as $player) {
            //Проверка возможности игрока увеличить ставку
            if ($player->getBalance() < $amount) {
                return false;
            }

            return true;
        }
    }

}
