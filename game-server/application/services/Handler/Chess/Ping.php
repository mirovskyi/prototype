<?php

 
class App_Service_Handler_Chess_Ping extends App_Service_Handler_Abstract
{

    /**
     * Получение объекта игры в шахматы
     *
     * @return Core_Game_Chess
     */
    public function getChess()
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
        //Проверка соответствия порядкового номера изменения данных
        if ($this->getRequest()->command != $this->getChess()->getCommand()) {
            //Возвращаем объект ответа с актувльными данными игры
            return $this->_getResponse(true);
        } else {

            //Проверка статуса игры, если игра окончена дальнейшие проверки не нужны
            if ($this->getChess()->getStatus() != Core_Game_Abstract::STATUS_PLAY) {
                //Возвращаем ответ на PING
                return $this->_getResponse();
            }

            //Проверка истечения времени партии
            if ($this->_isTimeout()) {
                //Получаем объект активного игрока
                $activePlayer = $this->getChess()->getPlayersContainer()->getActivePlayer();
                //Получаем оппонента активного игрока, для установки его как победителя партии
                $iterator = $this->getChess()->getPlayersContainer()->getIterator();
                $iterator->setCurrentElement($activePlayer);
                $opponent = $iterator->nextElement();
                //Завершение игры
                $this->_finishGame($opponent);
                //Возвращаем актуальные данные игры
                return $this->_getResponse(true);
            }

            //Возвращаем ответ на PING без ищменений
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
        $activePlayer = $this->getChess()->getPlayersContainer()->getActivePlayer();
        $gameTime = $this->getChess()->getPlayerGametime($activePlayer, false);

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
     * @param Core_Game_Players_Player $winner Объект игрока-победителя
     * @return mixed
     */
    protected function _finishGame(Core_Game_Players_Player $winner)
    {
        //Лочим данные сессии
        $this->getGameSession()->lockAndUpdate();
        //Проверяем изменение данных игры
        if ($this->getChess()->getCommand() != $this->getRequest()->get('command')) {
            //Снимаем блокировку данных сессии
            $this->getGameSession()->unlock();
            return;
        }

        //Проверяем совершил ли активный пользователь хоть один ход в партии
        $activePlayer = $this->getChess()->getPlayersContainer()->getActivePlayer();
        if ($this->getChess()->getChessBoard()->hasMovement($activePlayer->getId())) {
            //Игрок был активен в партии, установка оппоненту очка за победу
            $winner->addPoints(1);
            //Завершение партии
            $this->getChess()->finishGame();
        } else {
            //Игрок не сделал ни одного хода в партии - проигран весь матч
            //Установка победителя в матче
            $this->getChess()->setWinner($winner);
            //Завершение игры
            $this->getChess()->setStatus(Core_Game_Abstract::STATUS_FINISH);
        }
        //Обновление состояния игры (увеличиваем счетчик изменений)
        $this->getChess()->updateGameState();
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
            'game' => $this->getChess(),
            'chat' => $chat
        ));

        //Если флаг установлен, меняем шаблон и передаем данные игроков
        if ($update) {
            //Меняем шаблон
            $this->view->setTemplate('chess/update');
            //Передача данных игрового стола в шаблон
            $this->_assignViewGameData(false);
        }

        //Формирование ответа
        return $this->view->render();
    }

}
