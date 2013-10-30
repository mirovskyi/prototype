<?php

/**
 * Description of Update
 *
 * @author aleksey
 */
class App_Service_Handler_Checkers_Update extends App_Service_Handler_Abstract
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
     * Обработка запроса
     *
     * @return string
     * @throws Exception
     */
    public function handle()
    {
        //Создание ключа блокировки сессии
        $this->getGameSession()->lockAndUpdate();
        //Проверка актуальности запроса
        if ($this->getRequest()->get('command') != $this->getCheckers()->getCommand()) {
            //Удаляем ключ блокировки сессии
            $this->getGameSession()->unlock();
            //Возвращаем ответ с актуальными данными игры
            return $this->_getResponse();
        }

        //Обработка запроса
        try {
            $this->_handle();
        } catch (Exception $e) {
            //Удаляем ключ блокировки сессии
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Записываем измененные данные в хранилище
        $this->getGameSession()->saveAndUnlock();

        //Возвращаем объект ответа обновления данных игры
        return $this->_getResponse();
    }
    
    /**
     * Обработка команды обновления данных игры
     */
    protected function _handle()
    {
        //Проверка валидности команды обновления данных
        $this->_validate();
        
        //Обновление времени партии игрока (проверка истечения времени партии)
        $isTimeout = $this->_updateTimeout();

        //Инкремент идентификатора команды обновления данных игры
        $this->getCheckers()->incCommand();

        //Если время партии не истекло, обрабатываем ход
        if (!$isTimeout) {
            $this->_move();
        }

        //Обновляем время последнего изменения данных игры
        $this->getCheckers()->setLastUpdate();
    }
    
    /**
     * Проверка валидности команды обновления данных
     */
    protected function _validate()
    {
        //Получаем объект выбранной шашки
        $piece = $this->getCheckers()->getBoard()
                                     ->getPiece($this->getRequest()->get('pieceposition'));
        if (!$piece) {
            throw new Core_Game_Checkers_Exception('No piece for the given position', 2051, Core_Exception::USER);
        }

        //Получаем данные активного игрока
        $activePlayer = $this->getCheckers()->getPlayersContainer()->getActivePlayer();
        //Проверка очередности хода
        if ($this->getUserSession()->getSid() != $activePlayer->getSid()) {
            throw new Core_Game_Checkers_Exception('Wrong order of data update', 205, Core_Exception::USER);
        }

        //Проверка передвижения своей шашки
        if ($piece->getColor() != $activePlayer->getId()) {
            throw new Core_Game_Checkers_Exception('Try to move opponent piece', 2053, Core_Exception::USER);
        }
    }
    
    /**
     * Перемещение шашки
     */
    protected function _move()
    {
        //Перемещение шашки
        $fromPosition = $this->getRequest()->get('pieceposition');
        $toPosition = $this->getRequest()->get('moveposition');
        $piece = $this->getCheckers()->move($fromPosition, $toPosition);
        
        //Проверка наличия ничьи
        if ($piece->getBoard()->isDraw()) {
            //Завершения партии без начисления очков игрокам
            $this->getCheckers()->finishGame();
            return;
        }

        //Цвет шашек победителя
        $winnerColor = null;
        //Проверка наличия победителя (все шашки соперника убиты)
        $strBoard = $piece->getBoard()->__toString();
        $arrOpponentPieces = explode(':', $strBoard);
        if ($arrOpponentPieces[0] == '') {
            //У белых не осталось шашек, победа черных
            $winnerColor = Core_Game_Checkers_Piece::BLACK;
        } elseif ($arrOpponentPieces[1] == '') {
            //У черных не осталось шашек, победа белых
            $winnerColor = Core_Game_Checkers_Piece::WHITE;
        } else {

            //Получаем объект активного игрока
            $active = $this->getCheckers()->getPlayersContainer()->getActivePlayer();
            //Получаем цвет шашек оппонента активного игрока
            $opponentId = $this->getCheckers()->getOpponentColor($active->getId());

            //Проверка наличия возможности перемещения хоть одной своей шашки
            if (!$this->getCheckers()->getBoard()->hasMove($piece->getColor())) {
                //Все шашки заблокированы, победа оппонента активного игрока
                $winnerColor = $opponentId;
            }
            //Проверка наличия возможности перемещения шашки у соперника
            elseif (!$this->getCheckers()->getBoard()->hasMove($opponentId)) {
                //Все шашки соперника заблокированы, победа ходившего игрока
                $winnerColor = $active->getId();
            }
        }

        //Если есть победитель, обрабатываем окончание партии
        if (null !== $winnerColor) {
            $this->_setWinnerColor($winnerColor);
        } else {
            //Переключаем активного игрока
            $this->getCheckers()->getPlayersContainer()->switchActivePlayer();
        }
    }
                
    /**
     * Обновление времени партии текущего игрока
     *
     * @return bool
     */
    protected function _updateTimeout()
    {
        //Флаг истечения времени партии игрока
        $isTimeout = false;

        //Получаем объект активного игрока
        $activePlayer = $this->getCheckers()->getPlayersContainer()->getActivePlayer();
        //Получаем остаток времени игрока на партию
        $timeout = $activePlayer->getRestGametime($this->getCheckers()->getLastUpdate(), false);

        //Проверка истечения времени партии игрока
        if ($timeout < 0) {
            //Установка флага истечения времени партии
            $isTimeout = true;
            //Если игрок сделал хоть один ход за партию - проиграна одна партия
            if ($this->getCheckers()->getBoard()->hasMovement($activePlayer->getId())) {
                //Установка оппонента как победителя партии, завершение партии
                $this->_setWinnerColor($this->getCheckers()->getOpponentColor($activePlayer->getId()));
            } else {
                //Игрок не сделал ни одного хода в партии - проигран весь матч
                //Получение объекта оппонента активного игрока
                $iterator = $this->getCheckers()->getPlayersContainer()->getIterator();
                $iterator->setCurrentElement($activePlayer);
                $opponent = $iterator->nextElement();
                //Установка оппонента как победителя в матче
                $this->getCheckers()->setWinner($opponent);
                //Завершение игры
                $this->getCheckers()->setStatus(Core_Game_Abstract::STATUS_FINISH);
            }
            //Обнуляем время партии для корректного отображения данных игрока
            $timeout = 0;
        }

        //Установка остатка времени партии игрока
        $activePlayer->setGametime($timeout);

        //Позвращаем флаг истечения времени партии игрока
        return $isTimeout;
    }

    /**
     * Установка победителя
     *
     * @param int $color Цвет шашек победителя
     */
    protected function _setWinnerColor($color)
    {
        //Поиск игрока с шашками указанного цвета
        $winner = $this->getCheckers()->getPlayersContainer()->find('id', $color);
        //Добавляем одно очкл за победу
        $winner->addPoints(1);
        //Завершение партии
        $this->getCheckers()->finishGame();
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
        return $this->view->render();
    }
}