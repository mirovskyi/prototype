<?php

/**
 * Класс фигуры ладьи на шахмотной доске
 *
 * @author aleksey
 */
class Core_Game_Chess_Piece_Rook extends Core_Game_Chess_Piece_Abstract
{
    
    /**
     * Начальные позиции фигур ладьи, при которых возможна рокировка
     */
    const CASTLING_SHORT_WHITE = 'H1';
    const CASTLING_SHORT_BLACK = 'H8';
    const CASTLING_LONG_WHITE = 'A1';
    const CASTLING_LONG_BLACK = 'A8';
    
    /**
     * Системное имя фигуры
     *
     * @var string 
     */
    protected $_name = Core_Game_Chess_Piece::ROOK;


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
        //Запоминаем исходную позицию фигуры и цвет
        $pos = $this->getPosition()->getPosition();
        $color = $this->getColor();
        //Перемещение фигуры
        if (parent::move($position, $command)) {
            //Изменение флага возможности рокировки
            if ($pos == self::CASTLING_SHORT_WHITE 
                    && $color == Core_Game_Chess_Piece_Abstract::WHITE) {
                $this->getBoard()->setShortCastling($color, false);
            } elseif ($pos == self::CASTLING_SHORT_BLACK 
                    && $color == Core_Game_Chess_Piece_Abstract::BLACK) {
                $this->getBoard()->setShortCastling($color, false);
            } elseif ($pos == self::CASTLING_LONG_WHITE 
                    && $color == Core_Game_Chess_Piece_Abstract::WHITE) {
                $this->getBoard()->setLongCastling($color, false);
            } elseif ($pos == self::CASTLING_LONG_BLACK 
                    && $color == Core_Game_Chess_Piece_Abstract::BLACK) {
                $this->getBoard()->setLongCastling($color, false);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Валидация перемещения фигуры
     *
     * @param Core_Game_Chess_Coords_Position $position
     * @return boolean 
     */
    public function valid(Core_Game_Chess_Coords_Position $position) 
    {
        //Координаты текущего положения
        $oldX = $this->getPosition()->getHorizontalIndex();
        $oldY = $this->getPosition()->getVerticalIndex();
        //Координаты позиции перемещения
        $x = $position->getHorizontalIndex();
        $y = $position->getVerticalIndex();
        //Проверка перемещения по вертикали либо горизонтали
        if ($oldX == $x || $oldY == $y) {
            return $this->_validMove($position);
        }
        
        return false;
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
        
        //Проверка возможности хода по горизонтали
        for($i = 0; $i <= $n; $i++) {
            $position = new Core_Game_Chess_Coords_Position(array($i, $y));
            if ($this->_validMove($position) && !$this->_isCheckIfMove($position)) {
                //Перемещение возможно
                return true;
            }
        }
        
        //Проверка возможности хода по вертикали
        for($j = 0; $j <= $n; $j++) {
            $position = new Core_Game_Chess_Coords_Position(array($x, $j));
            if ($this->_validMove($position) && !$this->_isCheckIfMove($position)) {
                //Перемещение возможно
                return true;
            }
        }
        
        //Нет доступных позиций для перемещения фигуры
        return false;
    }
    
}