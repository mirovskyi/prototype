<?php

/**
 * Класс фигуры короля на шахмотной доске
 *
 * @author aleksey
 */
class Core_Game_Chess_Piece_King extends Core_Game_Chess_Piece_Abstract
{
    
    const INITIAL_POSITION_WHITE = 'E1';
    const INITIAL_POSITION_BLACK = 'E8';
    
    /**
     * Системное имя фигуры
     *
     * @var string 
     */
    protected $_name = Core_Game_Chess_Piece::KING;
    
    /**
     * Объект ладьи для рокировки
     *
     * @var Core_Game_Chess_Piece_Rook 
     */
    private $_castlingRook;


    /**
     * Перемещение фигуры на указанную позицию
     *
     * @param Core_Game_Chess_Coords_Position $position Объект позиции перемещения фигуры
     * @param int                             $command  Текущий идентификатор команды обновления данных игры
     *
     * @return bool
     */
    public function move(Core_Game_Chess_Coords_Position $position, $command = null)
    {
        //Проверка перемещения
        if (!parent::move($position, $command)) {
            return false;
        }

        //Убираем флаги возможности рокировки
        $this->getBoard()->setShortCastling($this->getColor(), false);
        $this->getBoard()->setLongCastling($this->getColor(), false);

        //Проверка рокировки (наличие ссылки на объект ладьи для рокировки)
        if ($this->_castlingRook instanceof Core_Game_Chess_Piece_Rook) {
            $rook = $this->_castlingRook;
            //Получаем координаты новой позиции ладьи
            $rookHorisontalIndex = $rook->getPosition()->getHorizontalIndex();
            $kingHorizontalIndex = $this->getPosition()->getHorizontalIndex();
            //Получаем индекс клетки ладьи по горизонтали
            if ($rookHorisontalIndex < $kingHorizontalIndex) {
                $rookHorisontalIndex = $kingHorizontalIndex + 1;
            } else {
                $rookHorisontalIndex = $kingHorizontalIndex - 1;
            }
            //Новая позиция ладьи
            $rookPosition = new Core_Game_Chess_Coords_Position(array(
                $rookHorisontalIndex,
                $this->getPosition()->getVerticalIndex()
            ));
            //Перемещение ладьи
            $oldPosition = $rook->getPosition()->getPosition();
            $this->getBoard()->unsetPiece($rook->getPosition());
            $rook->setPosition($rookPosition);
            $this->getBoard()->addPiece($rook);
            //Запись перемещения ладьи в историю анимаций
            if (null !== $command) {
                $this->getBoard()->getAnimation()->addAction(
                    $command,
                    Core_Game_Chess_Animation::MOVE,
                    $oldPosition,
                    $rookPosition->getPosition()
                );
            }
        }
        
        return true;
    }
    
    /**
     * Валидация перемещения фигуры
     *
     * @param Core_Game_Chess_Coords_Position $position
     * @return boolean 
     */
    public function valid(Core_Game_Chess_Coords_Position $position) 
    {
        //Очистка ссылки на объект ладьи для рокировки
        $this->_castlingRook = null;
        //Смещение
        $shiftX = abs($this->getPosition()->getHorizontalIndex() - 
                        $position->getHorizontalIndex());
        $shiftY = abs($this->getPosition()->getVerticalIndex() - 
                        $position->getVerticalIndex());
        //Проверка рокировки
        $pos = $this->getPosition()->getPosition();
        if ($shiftY == 0 && $shiftX == 2 && 
                ($pos == self::INITIAL_POSITION_WHITE || $pos == self::INITIAL_POSITION_BLACK)) {
            //Проверка возможности ракировки
            return $this->_validCastling($position);
        }
        //Проверка валидности хода
        if ($shiftX > 1 || $shiftY > 1) {
            return false;
        }
        
        //Проверка возможности передвижения фигуры в указанную позицию
        return $this->_validMove($position);
    }
    
    /**
     * Проверка наличия позиции на шахматной доске для валидного перемещения фигуры (для определения ПАТА)
     *
     * @return boolean
     */
    public function hasValid() 
    {
        //Координаты фигуры на доске
        $x = $this->getPosition()->getHorizontalIndex();
        $y = $this->getPosition()->getVerticalIndex();
        
        //Размерность квадратной матрицы доски (с учетом того что первый элемент - 0)
        $n = count(Core_Game_Chess_Coords_Position::$_horizontal) - 1; 
        
        //Проходим по всем ячейкам вокруг фигуры
        for($i = -1; $i <= 1; $i++) {
            for($j = -1; $j <= 1; $j++) {
                $xx = $x + $i;
                $yy = $y + $j;
                //Проверка существования позиции на доске
                if ($xx < 0 || $xx > $n || $yy < 0 || $yy > $n) {
                    continue;
                }
                //координаты позиции
                $pos = new Core_Game_Chess_Coords_Position(array($xx, $yy));
                //Проверка возможности перемещения на найденую позицию
                if ($this->_validMove($pos) && !$this->_isCheckIfMove($pos)) {
                    return true; //Есть возможность перемещения фигуры
                }
            }
        }
        
        //Нет доступных позиций для перемещения фигуры
        return false;
    }
    
    /**
     * Проверка возможности рокировки
     *
     * @param Core_Game_Chess_Coords_Position $position Позиция перемещения короля при рокировке
     * @return boolean
     */
    protected function _validCastling(Core_Game_Chess_Coords_Position $position)
    {
        //Проверка наличия шаха
        if ($this->getBoard()->getEvent() == Core_Game_Chess_Board::CHECK) {
            return false;
        }
        //Проверка возможности прохода на позицию перемещения 
        if (!$this->_validMove($position, true)) {
            return false;
        }
        //Получаем направление рокировки
        if ($position->getHorizontalIndex() > $this->getPosition()->getHorizontalIndex()) {
            //Проверяем совершалась ли рокировка ранее либо были ли перемещения короля или ладьи
            if (!$this->getBoard()->canShortCastling($this->getColor())) {
                return false;
            }
            //Получаем позицию ладьи
            if ($this->getColor() == Core_Game_Chess_Piece_Abstract::WHITE) {
                $rookPos = Core_Game_Chess_Piece_Rook::CASTLING_SHORT_WHITE;
            } else {
                $rookPos = Core_Game_Chess_Piece_Rook::CASTLING_SHORT_BLACK;
            }
            //Создание позиции перемещения ладьи
            $rookPosition = new Core_Game_Chess_Coords_Position(array(
                $position->getHorizontalIndex() - 1,
                $position->getVerticalIndex()
            ));
        } else {
            //Проверяем совершалась ли рокировка ранее либо были ли перемещения короля или ладьи
            if (!$this->getBoard()->canLongCastling($this->getColor())) {
                return false;
            }
            //Получаем позицию ладьи
            if ($this->getColor() == Core_Game_Chess_Piece_Abstract::WHITE) {
                $rookPos = Core_Game_Chess_Piece_Rook::CASTLING_LONG_WHITE;
            } else {
                $rookPos = Core_Game_Chess_Piece_Rook::CASTLING_LONG_BLACK;
            }
            //Создание позиции перемещения ладьи
            $rookPosition = new Core_Game_Chess_Coords_Position(array(
                $position->getHorizontalIndex() + 1,
                $position->getVerticalIndex()
            ));
        }
        
        //Проверяем наличие ладьи
        $rook = $this->getBoard()->getPiece($rookPos);
        if (!$rook instanceof Core_Game_Chess_Piece_Rook) {
            return false;
        }
        
        //Проверка возможности перемещения ладьи
        if(!$rook->valid($rookPosition)) {
            return false;
        }
        
        //Позиция перемещения короля и позиция, которую перепрыгивает король (позиция перемещения ладьи), не должны быть под ударом
        foreach($this->getBoard()->getPieces() as $line) {
            foreach($line as $piece) {
                if ($piece->getColor() != $this->getColor()) {
                    //Проверка возможности перемещения на позиции рокировки
                    if ($piece->valid($position)) {
                        return false;
                    }
                    if ($piece->valid($rookPosition)) {
                        return false;
                    }
                }
            }
        }
        
        //Установка объекта ладьи для рокировки
        $this->_castlingRook = $rook;
        return true;
    }
    
}