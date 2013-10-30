<?php

/**
 * Description of Update
 *
 * @author aleksey
 */
class App_Service_Handler_Filler_Update extends App_Service_Handler_Abstract
{

    /**
     * Получение объекта игры
     *
     * @return Core_Game_Filler_Abstract
     */
    public function getFiller()
    {
        return $this->getGameSession()->getData();
    }

    /**
     * Обработка запроса обновления данных игры
     *
     * @throws Exception
     * @return string
     */
    public function handle()
    {
        //Создание ключа блокировки сессии игры
        $this->getGameSession()->lockAndUpdate();
        //Проверка актуальности запроса
        if ($this->getRequest()->get('command') != $this->getFiller()->getCommand()) {
            //Удаление ключа блокировки сессии игры
            $this->getGameSession()->unlock();
            //Возвращаем актуальные данные игры
            return $this->_getResponse();
        }

        //Обработка запроса
        try {
            $this->_updateGameCondition();
        } catch (Exception $e) {
            //Удаление ключа блокировки сессии игры
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохраняем данные сессии
        $this->getGameSession()->saveAndUnlock();
        
        //Возвращаем ответ сервера
        return $this->_getResponse();
    }

    /**
     * Обновление данных текущего состояния игры
     *
     * @throws Core_Protocol_Exception
     * @return void
     */
    protected function _updateGameCondition()
    {
        //Получаем объект активного пользователя
        $activePlayer = $this->getFiller()->getPlayersContainer()->getActivePlayer();

        //Проверяем очередность хода
        if ($activePlayer->getSid() != $this->getUserSession()->getSid()) {
            throw new Core_Protocol_Exception('Wrong order of data update', 205, Core_Exception::USER);
        }

        //Установка выбранного цвета активным игроком
        $this->getFiller()->selectColor($this->getRequest()->get('color'), $activePlayer);
        //Изменение цвета полей активного пользователя
        $activePlayer->setColor($this->getRequest()->get('color'));

        //Обновление остатка времени на партию у активного игрока
        $timeout = $activePlayer->getRestGametime($this->getFiller()->getLastUpdate());
        $activePlayer->setGametime($timeout);

        //Проверка наличия победителя в партии
        $winner = $this->getFiller()->checkForWinner();
        if (false !== $winner) {
            //Добавление очка игроку за победу, установка статуса победителя
            $this->getFiller()->getPlayersContainer()->getPlayer($winner)->addPoints(1);
            //Завершение партии
            $this->getFiller()->finishGame($winner->getSid());
        } else {
            //Игра не завершена, переход хода к оппоненту
            $this->getFiller()->getPlayersContainer()->switchActivePlayer();
        }

        //Обновление состояния игры
        $this->getFiller()->updateGameState();
    }

    /**
     * Формирование ответа сервера
     *
     * @return mixed|string
     */
    protected function _getResponse()
    {
        //Передача данных игрового стола в шаблон
        $this->_assignViewGameData();
        return $this->view->render();
    }
}