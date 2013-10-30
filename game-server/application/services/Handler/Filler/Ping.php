<?php

/**
 * Description of Ping
 *
 * @author aleksey
 */
class App_Service_Handler_Filler_Ping extends App_Service_Handler_Abstract
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
     * Инициализация обработчика
     *
     * @throws Exception
     * @return void
     */
    public function init()
    {
        //Уничтожение обработанных событий
        $this->_destroyHandledEvents();
        //Проверка старта новой партии
        $this->_checkForRestartGame();
    }
    
    /**
     * Обработка пинг-запроса
     *
     * @return string
     */
    public function handle()
    {
        //Проверка наличия изменения данных игры
        if ($this->getRequest()->get('command') != $this->getFiller()->getCommand()) {
            //Возвращаем объект ответа с измененными данными игры
            return $this->_getResponse(true);
        }

        //Проверка истечения времени партии
        if ($this->_isTimeout()) {
            //Возвращаем обновленные данные игры
            return $this->_getResponse(true);
        } else {
            //Возвращаем ответ на PING без изменений
            return $this->_getResponse();
        }
    }

    /**
     * Проверка истечения таймаута партии
     *
     * @return bool
     */
    protected function _isTimeout()
    {
        //Получаем объект активного игрока
        $activePlayer = $this->getFiller()->getPlayersContainer()->getActivePlayer();
        //Получяем остаток времени на партию активного игрока
        $timeout = $this->getFiller()->getPlayerGametime($activePlayer, false);

        //Проверка истечения времени на патрию у активного игрока
        if ($timeout < 0) {
            //Получаем оппонента активного игрока
            $playersIterator = $this->getFiller()->getPlayersContainer()->getIterator();
            $playersIterator->setCurrentElement($activePlayer);
            $opponent = $playersIterator->nextElement();
            //Установка оппонента как победителя, завершение игры
            $this->_finishGame($opponent);
            return true;
        }

        return false;
    }

    /**
     * Завершение игры
     *
     * @param Core_Game_Players_Player $winner Объект игрока победителя
     * @return void
     */
    public function _finishGame(Core_Game_Players_Player $winner)
    {
        //Создание ключа блокировки сессии игры
        $this->getGameSession()->lockAndUpdate();
        //Проверка актуальности запроса
        if ($this->getRequest()->get('command') != $this->getFiller()->getCommand()) {
            //Данные запроса не актуальны, удаляем ключ блокировки сессии игры
            $this->getGameSession()->unlock();
            return;
        }

        //Проверка совершения действий активного игрока в текущей партии
        $activePlayer = $this->getFiller()->getPlayersContainer()->getActivePlayer();
        if ($this->getFiller()->hasPlayerAction($activePlayer->getId())) {
            //Активный игрок совершал действия в текущей партии - проиграна только партия
            $this->getFiller()->getPlayersContainer()->getPlayer($winner)->addPoints(1);
            //Завершение партии
            $this->getFiller()->finishGame($winner->getSid());
        } else {
            //Активный игрок не совершал действия в текущей партии - проиграна вся игра
            //Установка победителя
            $this->getFiller()->setWinner($winner);
            //Завершаем игру
            $this->getFiller()->setStatus(Core_Game_Abstract::STATUS_FINISH);
        }

        //Обновляем состояние игры
        $this->getFiller()->updateGameState();

        //Сохраняем данные сессии игры
        $this->getGameSession()->saveAndUnlock();
    }

    /**
     * Получение ответа сервера
     *
     * @param bool $update Флаг передачи актуальных данных игры
     * @return mixed|string
     */
    protected function _getResponse($update = false)
    {
        //Данные чата
        $chat = App_Model_Session_GameChat::chat($this->getGameSession()->getSid())->saveXml(
            $this->getUserSession()->getSid(),
            $this->getRequest()->get('chatId', 0),
            $update
        );

        //Передача данных в шаблон вида
        $this->view->assign(array(
            'game' => $this->getFiller(),
            'chat' => $chat
        ));

        //Проверка обновлния данных игры
        if ($update) {
            //Передача данных игрового стола в шаблон
            $this->_assignViewGameData(false);

            //Отдаем шаблон обновления данных игры
            $this->view->setTemplate('filler/update');
        }

        //Возвращаем ответ сервера
        return $this->view->render();
    }

}