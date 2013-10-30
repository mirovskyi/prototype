<?php

/**
 * Description of Shape
 *
 * @author aleksey
 */
class Core_Game_Chess_Piece 
{
    
    /**
     * Фигуры щахматной доски
     */
    const KING = 'K';
    const QUEEN = 'Q';
    const ROOK = 'R';
    const BISHOP = 'B';
    const KNIGHT = 'H';
    const PAWN = 'P';
    
    /**
     * Получение объекта шахматной фигуры
     *
     * @param string $name Короткое имя фигуры (идентификатор)
     * @param Core_Game_Chess_Coords_Position|string $position Текущая позиция фигуры
     * @param Core_Game_Chess_Board $board [Optional] Объект шахматной доски
     * @param integer $color [Optional] Идентификатор цвета фигуры
     * @return Core_Game_Chess_Piece_Abstract 
     */
    public static function get($name, $position, $board = null, $color = Core_Game_Chess_Piece_Abstract::WHITE)
    {
        $name = strtoupper($name);
        if (is_string($position)) {
            $position = strtoupper($position);
            $position = new Core_Game_Chess_Coords_Position($position);
        }
        
        $r = new ReflectionClass(__CLASS__);
        $const = $r->getConstants();
        
        if (!($fullName = array_search($name, $const))) {
            throw new Core_Game_Chess_Exception('Unknown chess piece name given', 2056, Core_Exception::USER);
        }
        
        $pieceClassName = __CLASS__ . '_' . ucfirst(strtolower($fullName));
        if (!class_exists($pieceClassName)) {
            throw new Core_Game_Chess_Exception('Piece \'' . $fullName . '\' class does not exists');
        }
        
        return new $pieceClassName($position, $board, $color);
    }
    
}