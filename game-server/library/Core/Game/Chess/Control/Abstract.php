<?php

/**
 * Description of Abstract
 *
 * @author aleksey
 */
abstract class Core_Game_Chess_Control_Abstract 
{
    
    /**
     * Объект шахматной доски
     *
     * @var Core_Game_Chess_Board 
     */
    protected $_board;
    
    /**
     * Объект фигуры на шахматной доске
     *
     * @var Core_Game_Chess_Piece_Abstract 
     */
    protected $_piece;
    
    
    /**
     * __construct
     *
     * @param Core_Game_Chess_Board|Core_Game_Chess_Piece_Abstract $object 
     */
    public function __construct($object = null)
    {
        if ($object instanceof Core_Game_Chess_Board) {
            $this->setBoard($object);
        } elseif ($object instanceof Core_Game_Chess_Piece_Abstract) {
            $this->setPiece($object)
                 ->setBoard($object->getBoard());
        }
    }
    
    /**
     * Установка объекта шахматной доски
     *
     * @param Core_Game_Chess_Board $board
     * @return Core_Game_Chess_States_Abstract 
     */
    public function setBoard(Core_Game_Chess_Board $board)
    {
        $this->_board = $board;
        return $this;
    }
    
    /**
     * Получение объекта шахматной доски
     *
     * @return Core_Game_Chess_Board 
     */
    public function getBoard()
    {
        return $this->_board;
    }
    
    /**
     * Установка объекта фигуры на шахматной доске
     *
     * @param Core_Game_Chess_Piece_Abstract $piece
     * @return Core_Game_Chess_EventChecker_Abstract 
     */
    public function setPiece(Core_Game_Chess_Piece_Abstract $piece)
    {
        $this->_piece = $piece;
        return $this;
    }
    
    /**
     * Получение объекта фигуры на шахматной доске
     *
     * @return Core_Game_Chess_Piece_Abstract 
     */
    public function getPiece()
    {
        return $this->_piece;
    }
    
    /**
     * Проверка совершения события
     */
    abstract public function check();
    
}