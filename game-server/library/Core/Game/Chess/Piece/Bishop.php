<?php

/**
 * Класс фигуры слона на шахмотной доске
 *
 * @author aleksey
 */
class Core_Game_Chess_Piece_Bishop extends Core_Game_Chess_Piece_Abstract
{
    
    /**
     * Системное имя фигуры
     *
     * @var string 
     */
    protected $_name = Core_Game_Chess_Piece::BISHOP;
    
    
    /**
     * Валидация перемещения фигуры
     *
     * @param Core_Game_Chess_Coords_Position $position
     * @return boolean 
     */
    public function valid(Core_Game_Chess_Coords_Position $position) 
    {   
        //Проверка перемещения по диогонали 1
        $oldDiffXY = $this->getPosition()->getHorizontalIndex() - 
                        $this->getPosition()->getVerticalIndex();
        $diffXY = $position->getHorizontalIndex() - 
                        $position->getVerticalIndex();
        if ($oldDiffXY == $diffXY) {
            return $this->_validMove($position);
        }
        //Проверка перемещения по диогонали 2
        $oldSumXY = $this->getPosition()->getHorizontalIndex() + 
                        $this->getPosition()->getVerticalIndex();
        $sumXY = $position->getHorizontalIndex() + 
                        $position->getVerticalIndex();
        if ($oldSumXY == $sumXY) {
            return $this->_validMove($position);
        }
        
        //Проверка возможности передвижения фигуры в указанную позицию
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