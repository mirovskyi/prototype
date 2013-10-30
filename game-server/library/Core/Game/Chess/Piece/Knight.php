<?php

/**
 * Класс фигуры коня на шахмотной доске
 *
 * @author aleksey
 */
class Core_Game_Chess_Piece_Knight extends Core_Game_Chess_Piece_Abstract
{
    
    /**
     * Системное имя фигуры
     *
     * @var string 
     */
    protected $_name = Core_Game_Chess_Piece::KNIGHT;
    
    
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
        //Проверка валидности перемещения
        if (abs($x - $oldX) == 1 && abs($y - $oldY) == 2) {
            return !$this->_hasOwnPiece($position);
        }
        if (abs($x - $oldX) == 2 && abs($y - $oldY) == 1) {
            return !$this->_hasOwnPiece($position);
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
        
        //Формируем список возможных позиций
        $possiblePositions = array(
            array($x + 2, $y + 1),
            array($x - 2, $y + 1),
            array($x + 2, $y - 1),
            array($x - 2, $y - 1),
            array($x + 1, $y + 2),
            array($x - 1, $y + 2),
            array($x + 1, $y - 2),
            array($x - 1, $y - 2)
        );
        
        //Проверка возможности хода
        foreach($possiblePositions as $posCoords) {
            //Проверяем не вышли ли координаты за пределы доски
            if ($posCoords[0] < 0 || $posCoords[0] > $n || 
                    $posCoords[1] < 0 || $posCoords[1] > $n) {
                continue;
            }
            //Создание объекта позиции на шахматной доске
            $position = new Core_Game_Chess_Coords_Position($posCoords);
            //Проверка наличия своей фигуры на месте перемещения
            if (!$this->_hasOwnPiece($position)) {
                //Проверка шаха собственному королю после перемещения
                if (!$this->_isCheckIfMove($position)) {
                    //Перемещение возможно
                    return true;
                }
            }
        }
        
        //Нет доступных позиций для перемещения фигуры
        return false;
    }
    
    /**
     * Проверка наличия своей фигуры на позиции перемещения
     *
     * @param Core_Game_Chess_Coords_Position $position
     * @return boolean 
     */
    protected function _hasOwnPiece(Core_Game_Chess_Coords_Position $position)
    {
        $piece = $this->getBoard()->getPiece($position);
        if ($piece && $piece->getColor() == $this->getColor()) {
            return true;
        }
        
        return false;
    }
    
}