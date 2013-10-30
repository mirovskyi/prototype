<?php

/**
 * Description of Promotion
 *
 * @author aleksey
 */
class App_Service_Handler_Chess_Promotion extends App_Service_Handler_Abstract
{
    
    /**
     * Получение объекта игры
     *
     * @return Core_Game_Chess 
     */
    public function getChess()
    {
        return $this->getGameSession()->getData();
    }

    /**
     * Обработка запроса
     *
     * @return string
     * @throws Exception
     */
    public function handle()
    {
        //Создание ключа блокировки сессии игры
        $this->getGameSession()->lockAndUpdate();
        //Проверка актуальности запроса
        if ($this->getRequest()->get('command') != $this->getChess()->getCommand()) {
            //Удаляем ключ блокировки сессии игры
            $this->getGameSession()->unlock();
            //Возвращаем ответ с актуальными данными игры
            return $this->_getResponse();
        }

        //Обработка запроса
        try {
            $this->_handle();
        } catch (Exception $e) {
            //Удаляем ключ блокировки сессии игры
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохраняем данные сессии игры
        $this->getGameSession()->saveAndUnlock();

        //Отдаем ответ
        return $this->_getResponse();
    }
    
    /**
     * Обработка команды обновления данных игры
     */
    protected function _handle()
    {
        //Проверка валидности команды обновления данных
        $this->_validate();
        
        //Обновление времени партии игрока
        $timeout = $this->_updateTimeout();

        //Получаем объект активного игрока
        $activePlayer = $this->getChess()->getPlayersContainer()->getActivePlayer();

        //Инкремент идентификатора команды обновления данных игры
        $this->getChess()->incCommand();
        
        //Проверка истечения времени партии
        if ($timeout < 0) {
            //Объект реализации определения ничейной ситуации на шахматной доске
            $draw = new Core_Game_Chess_Control_Draw($this->getChess()->getChessBoard());
            //Проверка ничьи
            if ($draw->check(true, $activePlayer->getId())) {
                //Установка события "ничья"
                $this->getChess()->getChessBoard()->setEvent(Core_Game_Chess_Board::DRAW);
            } else {
                //Ничьи нет, время партии активного игрока истекло, оппонент победил.
                //Получаем объект оппонента
                $playersIterator = $this->getChess()->getPlayersContainer()->getIterator();
                $playersIterator->setCurrentElement($activePlayer);
                $opponent = $playersIterator->nextElement();
                //Устанавливаем оппонента в качестве победителя
                $this->getChess()->setWinner($opponent);
            }
            //Завершение игры
            $this->getChess()->setStatus(Core_Game_Abstract::STATUS_FINISH);
        } else {
            //Время партии не истекло, заменяем пешку
            $this->_promotion();
        }
        
        //Изменяем текущего активного игрока
        $this->getChess()->getPlayersContainer()->switchActivePlayer();
        //Обновляем время последнего изменения игры
        $this->getChess()->setLastUpdate();
    }

    /**
     * Проверка валидности команды обновления данных
     * @throws Core_Protocol_Exception
     */
    protected function _validate()
    {
        //Получаем объект активного игрока
        $activePlayer = $this->getChess()->getPlayersContainer()->getActivePlayer();
        //Проверка соответствия пользователя активному игроку
        if ($this->getUserSession()->getSid() != $activePlayer->getSid()) {
            throw new Core_Protocol_Exception('Wrong order of data update', 205, Core_Exception::USER);
        }
    }
    
    /**
     * Замена пешки
     * 
     * @return void
     */
    protected function _promotion()
    {
        //Получаем цвет фигур текущего активного игрока
        $color = $this->getChess()->getPlayersContainer()->getActivePlayer()->getId();

        //Поиск пешки на которую производится замена (которая прошла к противополножному краю шахматной доски по вертикали)
        foreach($this->getChess()->getChessBoard()->getPieces() as $line) {
            foreach($line as $piece) {
                if ($piece instanceof Core_Game_Chess_Piece_Pawn &&
                        $piece->getColor() == $color &&
                        $piece->isPromotion()) {
                    //Замена пешки
                    $newPiece = $piece->promotion($this->getRequest()->piecetype, $this->getChess()->getCommand());
                    break;
                }
            }
        }
        
        //Проверка совершения превращения пешки
        if (!isset($newPiece)) {
            return;
        }
        
        //Система анализа перемещения фигуры на шахматной доске
        $control = new Core_Game_Chess_Control($newPiece);
        //Анализ текущей ситуации на шахматной доске
        $control->analysisPieceMove();

        //Проверка мата
        if ($control->isCheckmate()) {
            //Устновка информации о мате
            $this->getChess()->getChessBoard()->setEvent(Core_Game_Chess_Board::CHECKMATE);
            //Устновка победителя
            $winner = $this->getChess()->getPlayersContainer()->find('id', $control->getWinner());
            $this->getChess()->setWinner($winner);
            //Изменяем статус игры на "завершенный"
            $this->getChess()->setStatus(Core_Game_Abstract::STATUS_FINISH);
            return;
        }

        //Проверка шаха
        if ($control->isCheck()) {
            //Устновка информации о шахе
            $this->getChess()->getChessBoard()->setEvent(Core_Game_Chess_Board::CHECK);
            return;
        }

        //Проверка ничьи
        if ($control->isDraw()) {
            //Изменяем статус игры на "завершенный"
            $this->getChess()->setStatus(Core_Game_Abstract::STATUS_FINISH);
            //Установка статусов ничьии игрокам
            $this->getChess()->setDraw();
            //Если ПАТ, устанавливаем информацию о пате
            if ($control->isPat()) {
                $this->getChess()->getChessBoard()->setEvent(Core_Game_Chess_Board::PAT);
            } else {
                $this->getChess()->getChessBoard()->setEvent(Core_Game_Chess_Board::DRAW);
            }
            return;
        }

        //Нет событий, очищаем данные события
        $this->getChess()->getChessBoard()->clearEvent();
    }
    
    /**
     * Обновление времени партии текущего игрока
     *
     * @return int Остаток времени на партию у активного игрока
     */
    protected function _updateTimeout()
    {
        //Получаем объект активного игрока
        $activePlayer = $this->getChess()->getPlayersContainer()->getActivePlayer();
        //Получаем остаток времени на партию у активног игрока
        $timeout = $activePlayer->getRestGametime($this->getChess()->getLastUpdate(), false);

        //Обновление остатка времени партии у активного игрока
        if ($timeout < 0) {
            $activePlayer->setGametime(0);
        } else {
            $activePlayer->setGametime($timeout);
        }

        return $timeout;
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
        //Формирование ответа
        $this->view->setTemplate($this->getGameSession()->getData()->getName() . '/update');
        return $this->view->render();
    }
    
}