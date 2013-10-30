<?php

 
class Core_Game_Chess_Control
{

    /**
     * Флаг наличия пата
     *
     * @var bool
     */
    protected $_isPat;

    /**
     * Флаг наличия шаха
     *
     * @var bool
     */
    protected $_isCheck;

    /**
     * Флаг наличия мата
     *
     * @var bool
     */
    protected $_isCheckmate;

    /**
     * Флаг наличия ничьи
     *
     * @var bool
     */
    protected $_isDraw;
    
    /**
     * Флаг наличия события превращения пешки
     *
     * @var bool 
     */
    protected $_isPromotion;

    /**
     * Цвет фигур победителя
     *
     * @var int
     */
    protected $_winner;

    /**
     * Объект фигуры совершившей ход
     *
     * @var Core_Game_Chess_Piece_Abstract
     */
    protected $_piece;


    /**
     * __construct
     *
     * @param Core_Game_Chess_Piece_Abstract|null $piece
     */
    public function __construct(Core_Game_Chess_Piece_Abstract $piece = null)
    {
        if (null !== $piece) {
            $this->setPiece($piece);
        }
    }

    /**
     * Установка объекта фигуры
     *
     * @param Core_Game_Chess_Piece_Abstract $piece
     * @return Core_Game_Chess_Control
     */
    public function setPiece(Core_Game_Chess_Piece_Abstract $piece)
    {
        $this->_piece = $piece;
        return $this;
    }

    /**
     * Получение объекта фигуры
     *
     * @return Core_Game_Chess_Piece_Abstract
     */
    public function getPiece()
    {
        return $this->_piece;
    }

    /**
     * Установка флага наличия пата
     *
     * @param bool $pat
     * @return Core_Game_Chess_Control
     */
    public function setPat($pat = true)
    {
        $this->_isPat = $pat;
        return $this;
    }

    /**
     * Проверка наличия пата
     *
     * @return bool
     */
    public function isPat()
    {
        return $this->_isPat;
    }

    /**
     * Установка флага наличия шаха
     *
     * @param bool $check
     * @return Core_Game_Chess_Control
     */
    public function setCheck($check = true)
    {
        $this->_isCheck = $check;
        return $this;
    }

    /**
     * Проверка наличия шаха
     *
     * @return bool
     */
    public function isCheck()
    {
        return $this->_isCheck;
    }

    /**
     * Установка флага наличия мата
     *
     * @param bool $checkmate
     * @return Core_Game_Chess_Control
     */
    public function setCheckmate($checkmate = true)
    {
        $this->_isCheckmate = $checkmate;
        return $this;
    }

    /**
     * Проверка наличия мата
     *
     * @return bool
     */
    public function isCheckmate()
    {
        return $this->_isCheckmate;
    }

    /**
     * Установка флага наличия ничьи
     *
     * @param bool $draw
     * @return Core_Game_Chess_Control
     */
    public function setDraw($draw = true)
    {
        $this->_isDraw = $draw;
        return $this;
    }

    /**
     * Проверка наличия ничьи
     *
     * @return bool
     */
    public function isDraw()
    {
        return $this->_isDraw;
    }
    
    /**
     * Установка флага события превращения пешки
     *
     * @param bool $promotion
     * @return Core_Game_Chess_Control
     */
    public function setPromotion($promotion = true)
    {
        $this->_isPromotion = $promotion;
        return $this;
    }

    /**
     * Проверка наличия события превращения пешки
     *
     * @return bool
     */
    public function isPromotion()
    {
        return $this->_isPromotion;
    }

    /**
     * Установка цвета фигур победителя
     *
     * @param int $color
     * @return Core_Game_Chess_Control
     */
    public function setWinner($color)
    {
        $this->_winner = $color;
        return $this;
    }

    /**
     * Получение цвета фигур победителя
     *
     * @return int
     */
    public function getWinner()
    {
        return $this->_winner;
    }

    /**
     * Анализ хода фигуры
     *
     * @throws Core_Game_Chess_Exception
     * @return Core_Game_Chess_Control
     */
    public function analysisPieceMove()
    {
        //Проверка угрозы собственного короля после перемещения фигуры
        if ($this->_isChackToOwnKing()) {
            throw new Core_Game_Chess_Exception('Invalid move, check for own king', 2055, Core_Exception::USER);
        }
        
        //Проверка превращения пешки
        if ($this->getPiece() instanceof Core_Game_Chess_Piece_Pawn &&
                $this->getPiece()->isPromotion()) {
            $this->setPromotion();
            return $this;
        }

        //Проверка установки противнику шаха или мата
        if (!$this->_checkmate()) {
            //Проверка пата
            $pat = new Core_Game_Chess_Control_Pat($this->getPiece());
            if ($pat->check()) {
                $this->setPat()
                     ->setDraw();
            } else {
                //Проверка ничьи
                $draw = new Core_Game_Chess_Control_Draw($this->getPiece());
                if ($draw->check()) {
                    $this->setDraw();
                }
            }
        }
        
        return $this;
    }

    /**
     * Проверка угрозы собствнного короля после хода фигуры
     *
     * @return bool
     */
    protected function _isChackToOwnKing()
    {
        $piece = $this->getPiece();
        //Получаем объект короля текущего игрока
        $king = $piece->getBoard()->getKing($piece->getColor());
        //Объект проверки шаха
        $check = new Core_Game_Chess_Control_Check($king);

        //Проверка наличия шаха
        return $check->check();
    }

    /**
     * Проверка установки шаха или мата противнику
     *
     * @return bool
     */
    protected function _checkmate()
    {
        //Цвет фигур противника
        if ($this->getPiece()->getColor() == Core_Game_Chess_Piece_Abstract::WHITE) {
            $color = Core_Game_Chess_Piece_Abstract::BLACK;
        } else {
            $color = Core_Game_Chess_Piece_Abstract::WHITE;
        }

        //Объект фигуры короля противника
        $king = $this->getPiece()->getBoard()->getKing($color);

        //Объект проверки шаха
        $check = new Core_Game_Chess_Control_Check($king);
        //Проверка наличия шаха
        if ($check->check()) {
            $this->setCheck();
        } else {
            return false;
        }

        //Объект проверки мата
        $checkmate = new Core_Game_Chess_Control_Checkmate($king);
        //Проверка наличия мата
        if ($checkmate->check($check->getOpponentPiece())) {
            $this->setCheckmate()
                 ->setWinner($check->getOpponentPiece()->getColor());
        }

        //МАТ
        return true;
    }

}
