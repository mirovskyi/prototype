<?php

/**
 * Description of Checkmate
 *
 * @author aleksey
 */
class Core_Game_Chess_Control_Checkmate extends Core_Game_Chess_Control_Abstract 
{
    
    /**
     * Проверка окончания игры (МАТА)
     *
     * @param Core_Game_Chess_Piece_Abstract $checkOpponentPiece [Optional] Объект фигуры, которая угрожает королю
     * @return boolean
     */
    public function check($checkOpponentPiece = null)
    {
        if (!$this->getPiece() instanceof Core_Game_Chess_Piece_King) {
            throw new Core_Game_Chess_Exception('Invalid piece type given in checkmate event');
        }
        
        //Проверка наличичя шаха
        if (!$checkOpponentPiece instanceof Core_Game_Chess_Piece_Abstract) {
            $check = new Core_Game_Chess_Control_Check($this->getPiece());
            if (!$check->check()) {
                return false;
            } else {
                $checkOpponentPiece = $check->getOpponentPiece();
            }
        }
        
        /**
         * Эмуляция всех возможных ходов для защиты короля и проверка наличия при этом шаха
         */
        //1) - перемещения короля на все возможные позиции
        if ($this->_isMoveSaveKing()) {
            return false;
        }
        //2) - возможность убить фигуру противника, угрожающую королю
        if ($this->_isKillOpponentSaveKing($checkOpponentPiece)) {
            return false;
        }
        //3) - возможность перекрыть доступ угрожающей фигуры соперника к королю
        if ($this->_canBlockAccessToKing($checkOpponentPiece)) {
            return false;
        }
        
        //МАТ
        return true;
    }
    
    /**
     * Проверка возможности избежания шаха путем изменения позиции короля
     *
     * @return boolean
     */
    protected function _isMoveSaveKing()
    {
        //Копируем объект короля (и данные шахматной доски)
        $king = clone ($this->getPiece());
        //По очереди перемещаем короля во все возможные позиции и проверяем наличие шаха
        $hIndex = $king->getPosition()->getHorizontalIndex();
        $vIndex = $king->getPosition()->getVerticalIndex();
        $maxHIndex = count(Core_Game_Chess_Coords_Position::$_horizontal) - 1;
        $maxVIndex = count(Core_Game_Chess_Coords_Position::$_vertical) - 1;
        for($h = $hIndex - 1; $h <= $hIndex + 1 && $h <= $maxHIndex; $h ++) {
            for($v = $vIndex - 1; $v <= $vIndex + 1 && $v <= $maxVIndex; $v ++) {
                if ($h >= 0 && $v >= 0 && 
                        ($h != $hIndex || $v != $vIndex)) {
                    //Перемещение фигуру короля
                    $position = new Core_Game_Chess_Coords_Position(array($h, $v));
                    if ($king->move($position)) {
                        //Проверка наличия шаха
                        $check = new Core_Game_Chess_Control_Check($king);
                        if (!$check->check()) {
                            //Шаха нет
                            return true;
                        }
                    }
                    //Возвращаем состояние доски в исходное положение
                    unset($king);
                    $king = clone ($this->getPiece());
                }
            }
        }
        
        return false;
    }
    
    /**
     * Проверка возможности избежания шаха путем уничтожения угрожающей фигуры соперника 
     *
     * @param Core_Game_Chess_Piece_Abstract $opponentPiece
     * @return boolean 
     */
    protected function _isKillOpponentSaveKing(Core_Game_Chess_Piece_Abstract $opponentPiece)
    {
        //Поиск фигуры, которая может убить фигуру противника, угрожающую королю
        foreach($this->getPiece()->getBoard()->getPieces() as $line) {
            foreach($line as $piece) {
                if ($opponentPiece->getColor() != $piece->getColor()) {
                    if ($piece->valid($opponentPiece->getPosition())) {
                        //Копируем состояние доски и убиваем угрожающую фигуру
                        $kingCopy = clone ($this->getPiece());
                        $pieceCopy = $kingCopy->getBoard()->getPiece($piece->getPosition());
                        $pieceCopy->move($opponentPiece->getPosition());
                        //Проверяем наличие шаха
                        $check = new Core_Game_Chess_Control_Check($kingCopy);
                        if (!$check->check()) {
                            return true;
                        }
                    }
                }
            }
        }

        //Если угрожающая фигура пешка, проверяем возможность убить ее взятием на проходе
        if ($opponentPiece instanceof Core_Game_Chess_Piece_Pawn) {
            //Проверка возможности взятия на проходе
            return $this->_isEnpassantSaveKing($opponentPiece);
        }
        
        return false;
    }
    
    /**
     * Проверка возможности избежания шаха путем блокировки доступа угрожающей фигуры соперника к королю
     *
     * @param Core_Game_Chess_Piece_Abstract $opponentPiece
     * @return boolean 
     */
    protected function _canBlockAccessToKing(Core_Game_Chess_Piece_Abstract $opponentPiece)
    {
        //В случае с конем проверка не нужна, перекрыть ему путь нельзя
        if ($opponentPiece instanceof Core_Game_Chess_Piece_Knight) {
            return false;
        }
        //Текущие позиции короля и фигуры соперника
        $kingX = $this->getPiece()->getPosition()->getHorizontalIndex();
        $kingY = $this->getPiece()->getPosition()->getVerticalIndex();
        $opponentX = $opponentPiece->getPosition()->getHorizontalIndex();
        $opponentY = $opponentPiece->getPosition()->getVerticalIndex();
        
        //Направление шага для прохода по горизонтали
        if ($kingX == $opponentX) {
            $stepX = 0;
        } else {
            $stepX = ($opponentX < $kingX) ? 1 : -1;
        }
        //Направление шага для прохода по вертикали
        if ($kingY == $opponentY) {
            $stepY = 0;
        } else {
            $stepY = ($opponentY < $kingY) ? 1 : -1;
        }
        
        //Текущие значения координат при проходе по движению фигуры соперника
        $x = $opponentX + $stepX;
        $y = $opponentY + $stepY;
        //Проход по пути передвижения фигуры соперника
        while($x != $kingX || $y != $kingY) {
            //Позиция на доске
            $tempPos = new Core_Game_Chess_Coords_Position(array($x, $y));
            //Проверка возможности перекрытия позиции
            foreach($this->getPiece()->getBoard()->getPieces() as $line) {
                foreach($line as $piece) {
                    if ($this->getPiece()->getColor() == $piece->getColor() &&
                            $piece->getName() != Core_Game_Chess_Piece::KING) {
                        if ($piece->valid($tempPos)) {
                            //Копируем состояние доски и закрываем доступ к королю
                            $kingCopy = clone ($this->getPiece());
                            $pieceCopy = $kingCopy->getBoard()->getPiece($piece->getPosition());
                            $pieceCopy->move($tempPos);
                            //Проверяем наличие шаха
                            $check = new Core_Game_Chess_Control_Check($kingCopy);
                            if (!$check->check()) {
                                return true;
                            }
                        }
                    }
                }
            }
            //Переход на следующую клетку
            $x += $stepX;
            $y += $stepY;
        }
        
        return false;
    }

    private function _isEnpassantSaveKing(Core_Game_Chess_Piece_Pawn $opponentPiece)
    {
        //Получаем текущее положение пешки
        $currentPosition = $opponentPiece->getPosition();

        //Проверка положения пешки (должна быть на две клетки впереди от начальной позиции)
        if ($opponentPiece->getColor() === Core_Game_Chess_Piece_Abstract::WHITE) {
            if ($currentPosition->getVerticalPosition() != 4) {
                return false;
            }
        } else {
            if ($currentPosition->getVerticalPosition() != 5) {
                return false;
            }
        }

        //Получаем предыдущее состояние шахмотной доски
        $previousBoard = $opponentPiece->getBoard()->getPreviousBoardObject();

        //Проверка предыдущего положения пешки (должно быть начальное положение)
        if ($opponentPiece->getColor() === Core_Game_Chess_Piece_Abstract::WHITE) {
            $startPosition = $currentPosition->getHorizontalPosition() . '2';
        } else {
            $startPosition = $currentPosition->getHorizontalPosition() . '7';
        }
        $startPosition = new Core_Game_Chess_Coords_Position($startPosition);
        if (!$previousBoard->getPiece($startPosition) instanceof Core_Game_Chess_Piece_Pawn) {
            return false;
        }

        //Проверка наличия по горизонтали от угрожающей пешки, пешки соперника
        for($i = -1; $i <= 1; $i = $i + 2) {
            //Смещение позиции по горизонтали
            $position = clone($currentPosition);
            if ($position->shift($i)) {
                $piece = $opponentPiece->getBoard()->getPiece($position);
                if ($piece && $piece->getColor() != $opponentPiece->getColor()) {
                    //Взятие пешки на проходе, угрожающей королю шахом
                    return true;
                }
            }
        }

        return false;
    }
}