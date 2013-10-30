<?php

/**
 * Description of Checkmate
 *
 * @author aleksey
 */
class Core_Game_Chess_Control_Check extends Core_Game_Chess_Control_Abstract 
{
    /**
     * Объект фигуры соперника, угрожающей королю шахом
     *
     * @var Core_Game_Chess_Piece_Abstract
     */
    protected $_opponentPiece;
    
    
    /**
     * Проверка наличия шаха
     *
     * @return boolean 
     */
    public function check()
    {
        if (!$this->getPiece() instanceof Core_Game_Chess_Piece_King) {
            throw new Core_Game_Chess_Exception('Invalid piece type given in checkmate event');
        }
        
        //Проверка возможности убить короля у каждой фигуры противника
        foreach($this->getPiece()->getBoard()->getPieces() as $line) {
            foreach($line as $piece) {
                if ($piece->getColor() != $this->getPiece()->getColor()) {
                    if ($piece->valid($this->getPiece()->getPosition())) {
                        //У фигуры есть возможность передвижения на позицию короля, ШАХ
                        $this->_opponentPiece = $piece;
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Получение объекта фигуры соперника, угрожающей королю шахом
     *
     * @return Core_Game_Chess_Piece_Abstract
     */
    public function getOpponentPiece()
    {
        return $this->_opponentPiece;
    }
    
}