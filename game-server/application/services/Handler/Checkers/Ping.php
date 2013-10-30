<?php

 
class App_Service_Handler_Checkers_Ping extends App_Service_Handler_Abstract
{

    /**
     * Получение объекта игры в шашки
     *
     * @return Core_Game_Checkers
     */
    public function getCheckers()
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
     * Обработка комманды PING
     *
     * @return string
     */
    public function handle()
    {
        //Получаем текущие данные игры
        $checkers = $this->getGameSession()->getData();
        //Проверка соответствия порядкового номера изменения данных
        if ($this->getRequest()->get('command') != $checkers->getCommand()) {
            //Возвращаем ответ с актувльными данными игры
            return $this->_getResponse(true);
        } else {

            //Проверка статуса игры, если игра окончена дальнейшие проверки не нужны
            if ($this->getCheckers()->getStatus() == Core_Game_Abstract::STATUS_FINISH) {
                //Возвращаем ответ на PING
                return $this->_getResponse();
            }

            //Проверка истечения времени партии активного игрока
            if ($this->_isTimeout()) {
                //Получаем объект победителя, оппонента активного игрока
                $container = $this->getCheckers()->getPlayersContainer();
                $container->getIterator()->setCurrentElement($container->getActivePlayer());
                $winner = $container->getIterator()->nextElement();
                //Изменяем данные игры (завершение игры)
                $this->_finishGame($winner);
                //Возвращаем ответ с измененными данными игры
                return $this->_getResponse(true);
            }
            //Возвращаем ответ на PING
            return $this->_getResponse();
        }
    }

    /**
     * Проверка истечения времени
     *
     * @return bool
     */
    protected function _isTimeout()
    {
        //Получаем остваток времени на партию у активного игрока
        $activePlayer = $this->getCheckers()->getPlayersContainer()->getActivePlayer();
        $gameTime = $this->getCheckers()->getPlayerGametime($activePlayer, false);

        //Проверка истечения времени партии активного игрока
        if ($gameTime < 0) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Установка победителя, завершение игры
     *
     * @param Core_Game_Players_Player $winner
     * @return mixed
     */
    protected function _finishGame(Core_Game_Players_Player $winner)
    {
        //Лочим данные сессии
        $this->getGameSession()->lockAndUpdate();
        //Проверяем изменение данных игры
        if ($this->getCheckers()->getCommand() != $this->getRequest()->get('command')) {
            //Снимаем блокировку данных сессии
            $this->getGameSession()->unlock();
            return;
        }

        //Проверка наличия хоть одного хода активного игрока в текущей партии
        $activePlayer = $this->getCheckers()->getPlayersContainer()->getActivePlayer();
        if ($this->getCheckers()->getBoard()->hasMove($activePlayer->getId())) {
            //Игрок у которого закончилось время ходил в текущей партии - проиграна одна партия
            $winner->addPoints(1);
            $this->getCheckers()->finishGame();
        } else {
            //Игрок у которого закончилось время не сделал ни одного хода в партии - проиграна вся игра
            //Установка победителя в матче
            $this->getCheckers()->setWinner($winner);
            //Установка статуса завершения игры
            $this->getCheckers()->setStatus(Core_Game_Abstract::STATUS_FINISH);
        }

        //Обновление состояния игры (увеличиваем счетчик изменений)
        $this->getCheckers()->updateGameState();
        //Сохранение данных сессии
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

        //Передаем данные игры в шаблон вида
        $this->view->assign(array(
            'game' => $this->getCheckers(),
            'chat' => $chat
        ));

        //Если флаг установлен, меняем шаблон и передаем данные игроков
        if ($update) {
            //Меняем шаблон
            $this->view->setTemplate('checkers/update');
            //Передача данных игрового стола в шаблон
            $this->_assignViewGameData(false);
        }

        //Возвращаем ответ сервера
        return $this->view->render();
    }

}
