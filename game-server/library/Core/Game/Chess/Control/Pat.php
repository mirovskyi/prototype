<?php

/**
 * Description of Pat
 *
 * @author aleksey
 */
class Core_Game_Chess_Control_Pat extends Core_Game_Chess_Control_Abstract 
{
    
    public function check()
    {
        if (!$this->getBoard() instanceof Core_Game_Chess_Board) {
            throw new Core_Exception('Invalid chess board object given to check a pat');
        }
        
        //Проход по всем фигурам на шахматной доске
        foreach($this->getBoard()->getPieces() as $line) {
            foreach($line as $piece) {
                if ($this->getPiece()->getColor() == $piece->getColor()) {
                    continue;
                }
                //Проверка возможности перемещения фигуры
                if ($piece->hasValid()) {
                    //Есть возможность перемещения, не ПАТ
                    return false;
                }
            }
        }
        
        //Ни у одной фигуры нет возможности перемещения - ПАТ
        return true;
    }
    
}