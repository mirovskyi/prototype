<?php

/**
 * Класс фигуры ферзя на шахмотной доске
 *
 * @author aleksey
 */
class Core_Game_Chess_Piece_Queen extends Core_Game_Chess_Piece_Abstract 
{
    
    /**
     * Системное имя фигуры
     *
     * @var string 
     */
    protected $_name = Core_Game_Chess_Piece::QUEEN;
    
    
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
        //Проверка перемещения по диогонали 1
        $oldDiffXY = $oldX - $oldY;
        $diffXY = $x - $y;
        if ($oldDiffXY == $diffXY) {
            return $this->_validMove($position);
        }
        //Проверка перемещения по диогонали 2
        $oldSumXY = $oldX + $oldY;
        $sumXY = $x + $y;
        if ($oldSumXY == $sumXY) {
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
        
        //Поиск координат диогонали паралельной побочной (доска квадратная!)
        $sumXY = $x + $y;
        for($i = 0; $i <= $n; $i++) {
            //Если мы находимся в точке текущей позиии фигуры, пропускаем ее
            if ($x == $i) {
                continue;
            }
            //Координаты по вертикали
            $yy = $sumXY - $i;
            //Проверка выхода за пределы матрицы
            if ($yy >= 0 && $yy <= $n) {
                //координаты позиции
                $pos = new Core_Game_Chess_Coords_Position(array($i, $yy));
                //Проверка возможности перемещения на найденую позицию
                if ($this->_validMove($pos) && !$this->_isCheckIfMove($pos)) {
                    return true; //Есть возможность перемещения фигуры
                }
            }
        }
        
        //Поиск координат диогонали паралельной главной (доска квадратная!)
        if ($x == $y) {
            $i = 0; $j = 0; //Главная доагональ
        } elseif ($x > $y) {
            $i = $x - $y; $j = 0; //Справа от главной диагонали
        } elseif ($x < $y) {
            $i = 0; $j = $y - $x; //Слева от главной диагонали
        }
        //Проходим по всем всем положениям в диагонали
        for($k = 0; $k <= $n; $k++) {
            $i += $k;
            $j += $k;
            //Проверка выхода за границы матрицы
            if ($i > $n || $j > $n) {
                break;
            }
            //Если находимся на текущем месте фигуры, пропускаем проверку
            if ($i == $x && $j == $y) {
                continue;
            }
            //координаты позиции
            $pos = new Core_Game_Chess_Coords_Position(array($i, $j));
            //Проверка возможности перемещения на найденую позицию
            if ($this->_validMove($pos) && !$this->_isCheckIfMove($pos)) {
                return true; //Есть возможность перемещения фигуры
            }
        }
        
        //Нет доступных позиций для перемещения фигуры
        return false;
    }
    
}