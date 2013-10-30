<?php

/**
 * Description of Update
 *
 * @author aleksey
 */
class App_Service_Handler_Chess_Update extends App_Service_Handler_Abstract
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
     * Обработка команды обновления данных игры
     *
     * @return string
     * @throws Exception
     */
    public function handle()
    {
        //Создаем ключ блокировки данных сессии игры
        $this->getGameSession()->lockAndUpdate();

        //Проверка актуальности данных игры
        if ($this->getRequest()->get('command') != $this->getChess()->getCommand()) {
            //Удаляем ключ блокировки данных игры
            $this->getGameSession()->unlock();
            //Возвращаем ответ с актуальными данными игры
            return $this->_getResponse();
        }

        //Обработка запроса
        try {
            $this->_handle();
        } catch (Exception $e) {
            //Удаляем ключ блокировки данных игры
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохраняем данные игры
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

        //Проверка наличия события превращения пешки
        if ($this->getChess()->getChessBoard()->hasEvent(Core_Game_Chess_Board::PROMOTION)) {
            //При "превращении" пешки можно только менять пешку на выбранную фигуру
            //т.е. делать запрос на сервер с методом chess.promotion. Обновление данных запрещено
            throw new Core_Exception('The movement of the piece is forbidden', 2054, Core_Exception::USER);
        }
        
        //Обновление времени партии игрока
        $timeout = $this->_updateTimeout();

        //Получаем объект активного пользователя
        $activePlayer = $this->getChess()->getPlayersContainer()->getActivePlayer();

        //Инкремент идентификатора команды обновления данных игры
        $this->getChess()->incCommand();
        
        //Проверка истечения времени партии
        if ($timeout < 0) {
            //время партии активного игрока истекло, оппонент победил.
            //Получаем объект оппонента
            $playersIterator = $this->getChess()->getPlayersContainer()->getIterator();
            $playersIterator->setCurrentElement($activePlayer);
            $opponent = $playersIterator->nextElement();
            //Проверяем совершил ли активный пользователь хоть один ход в партии
            if ($this->getChess()->getChessBoard()->hasMovement($activePlayer->getId())) {
                //Игрок был активен в партии, добавление очков за победу в партии оппонкнту
                $opponent->addPoints(1);
                //Завершение партии
                $this->getChess()->finishGame();
            } else {
                //Игрок не сделал ни одного хода в партии, считается проигранным весь матч
                //Устанавливаем оппонента в качестве победителя
                $this->getChess()->setWinner($opponent);
                //Завершение игры
                $this->getChess()->setStatus(Core_Game_Abstract::STATUS_FINISH);
            }
        } else {
            //Время партии не истекло, перемещаем фигуру
            $finishGame = $this->_move();
            //Если партия завершена, просто обновляем время последнего обновления игры
            if ($finishGame) {
                $this->getChess()->setLastUpdate();
            } else {
                //Партия не завершена, переключаем активного игрока
                if ($this->_switchActivPlayer()) {
                    //Активный пользователь переключен, обновляем время последнего изменения игры
                    $this->getChess()->setLastUpdate();
                }
            }
        }
    }
    
    /**
     * Проверка валидности команды обновления данных
     */
    protected function _validate()
    {
        //Получаем объект активного игрока
        $activePlayer = $this->getChess()->getPlayersContainer()->getActivePlayer();
        //Проверка соответствия текущего пользователя активному игроку
        if ($this->getUserSession()->getSid() != $activePlayer->getSid()) {
            throw new Core_Protocol_Exception('Wrong order of data update', 205, Core_Exception::USER);
        }

        //Получаем выбранную фигуру
        $position = $this->getRequest()->get('pieceposition');
        $piece = $this->getChess()->getChessBoard()->getPiece($position);
        if (!$piece) {
            throw new Core_Game_Chess_Exception('No piece for the given position', 2051, Core_Exception::USER);
        }

        //Проверка передвижения своей фигуры
        if ($piece->getColor() != $activePlayer->getId()) {
            throw new Core_Game_Chess_Exception('Try to move opponent piece', 2053, Core_Exception::USER);
        }
    }
    
    /**
     * Перемещение фигуры
     *
     * @return bool Возвращает флаг завершения партии
     */
    protected function _move()
    {
        //Объект игры
        $chess = $this->getChess();

        //Перемещение фигуры
        $piece = $chess->move(
            $this->getRequest()->pieceposition,
            $this->getRequest()->moveposition
        );
        
        //Система анализа перемещения фигуры на шахматной доске
        $control = new Core_Game_Chess_Control($piece);
        //Анализ текущей ситуации на шахматной доске
        $control->analysisPieceMove();

        //Проверка мата
        if ($control->isCheckmate()) {
            //Устновка информации о мате
            $chess->getChessBoard()->setEvent(Core_Game_Chess_Board::CHECKMATE);
            //Добавление очков победителю
            $winner = $chess->getPlayersContainer()->find('id', $control->getWinner());
            $winner->addPoints(1);
            //Завершение партии
            $chess->finishGame();
            return true;
        }

        //Проверка шаха
        if ($control->isCheck()) {
            //Устновка информации о шахе
            $chess->getChessBoard()->setEvent(Core_Game_Chess_Board::CHECK);
            return false;
        }

        //Проверка ничьи
        if ($control->isDraw()) {
            //Завершение партии
            $chess->finishGame();
            //Если ПАТ, устанавливаем информацию о пате
            if ($control->isPat()) {
                $chess->getChessBoard()->setEvent(Core_Game_Chess_Board::PAT);
            } else {
                $chess->getChessBoard()->setEvent(Core_Game_Chess_Board::DRAW);
            }
            return true;
        }

        //Проверка превращения пешки
        if ($control->isPromotion()) {
            //Установка события превращение пешки
            $chess->getChessBoard()->setEvent(Core_Game_Chess_Board::PROMOTION);
            return false;
        }

        //Нет событий, очищаем данные события
        $chess->getChessBoard()->clearEvent();
        return false;
    }
    
    /**
     * Обновление времени партии текущего игрока
     *
     * @return int Возвращает текущий остаток времени на партию активного игрока
     */
    protected function _updateTimeout()
    {
        //Получаем активного пользователя
        $activePlayer = $this->getChess()->getPlayersContainer()->getActivePlayer();
        //Получаем остаток времени партии игрока
        $timeout = $activePlayer->getRestGametime($this->getChess()->getLastUpdate(), false);

        //Обновление остатка времени на партию активного игрока
        if ($timeout < 0) {
            $activePlayer->setGametime(0);
        } else {
            $activePlayer->setGametime($timeout);
        }

        //Возвращаем остаток времени на партию
        return $timeout;
    }
    
    /**
     * Переключение активного пользователя
     *
     * @return bool
     */
    protected function _switchActivPlayer()
    {
        //Проверка наличия события прохода (превращения) пешки, при этом ход не передается, а ожидается запрос превращения пешки (chess.promotion)
        if (!$this->getChess()->getChessBoard()->hasEvent(Core_Game_Chess_Board::PROMOTION)) {
            //Переключение активного пользователя
            $this->getChess()->getPlayersContainer()->switchActivePlayer();
            return true;
        }

        return false;
    }

    /**
     * Формирование ответа сервера
     *
     * @return string
     */
    protected function _getResponse()
    {
        //Передача данных игрового стола в шаблон
        $this->_assignViewGameData();
        //Формирование ответа
        return $this->view->render();
    }
}