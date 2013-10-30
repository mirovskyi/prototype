<?php

/**
 * Description of Table
 *
 * @author aleksey
 */
class Core_Game_Chess_Board
{
    
    /**
     * События в игре
     */
    const CHECK = 'check';
    const CHECKMATE = 'checkmate';
    const PAT = 'pat';
    const DRAW = 'draw';
    const DRAWOFFER = 'drawoffer';
    const PROMOTION = 'promotion';
    
    /**
     * Данные шахматной доски (массив фигур)
     *
     * @var array  
     */
    protected $_pieces = array();
    
    /**
     * Ссылки на объекты фигур королей
     *
     * @var array 
     */
    protected $_kings = array();
    
    /**
     * Флаг возможности короткой рокировки
     *
     * @var array 
     */
    protected $_canShortCastling = array(
        Core_Game_Chess_Piece_Abstract::WHITE => true,
        Core_Game_Chess_Piece_Abstract::BLACK => true
    );
    
    /**
     * Флаг возможности длинной рокировки
     *
     * @var array 
     */
    protected $_canLongCastling = array(
        Core_Game_Chess_Piece_Abstract::WHITE => true,
        Core_Game_Chess_Piece_Abstract::BLACK => true
    );

    /**
     * История перемещений фигур
     *
     * @var array
     */
    protected $_history = array();
    
    /**
     * Событие (шах, мат, пат, ничья)
     *
     * @var string
     */
    protected $_event;
    
    /**
     * Предыдущее состояние шахматной доски
     *
     * @var string
     */
    protected $_previousBoardState;

    /**
     * Объект истории анимаций
     *
     * @var Core_Game_Chess_Animation
     */
    protected $_animation;


    /**
     * __construct
     */
    public function __construct()
    {
        //СОздание нового объекта истории анимации
        $this->setAnimation(new Core_Game_Chess_Animation());
    }

    /**
     * Преобразование данных объекта перед сериализацией
     * 
     * @return array
     */
    public function __sleep() 
    {
        //Преобразование данных доски в строковый формат
        $this->_pieces = $this->__toString();
        return array('_pieces',
                     '_history',
                     '_event',
                     '_canShortCastling',
                     '_canLongCastling',
                     '_previousBoardState',
                     '_animation');
    }
    
    /**
     * Восстановление данных объекта при unserialize
     */
    public function __wakeup() 
    {
        //Изменение типа поля списка фигур
        $strPieces = $this->getPieces();
        $this->_pieces = array();
        //Восстановление данных доски из строки
        $arrPieces = explode(':', $strPieces);
        //Расставление белых фигур на доску
        $this->fromString($arrPieces[0], Core_Game_Chess_Piece_Abstract::WHITE);
        //Расставление черных фигур на доску
        $this->fromString($arrPieces[1], Core_Game_Chess_Piece_Abstract::BLACK);
    }
    
    /**
     * Клонирование объекта
     */
    public function __clone()
    {
        //Инициализация новых фигур доски и добаление ссылок на них
        $this->setPieces($this->__toString());
    }
    
    /**
     * Установка фигур на шахматную доску
     *
     * @param array|string $pieces 
     */
    public function setPieces($pieces)
    {
        if (is_string($pieces)) {
            $this->_pieces = $pieces;
            $this->__wakeup();
            return;
        }
        
        foreach($pieces as $key => $val) {
            if (is_array($val)) {
                foreach($val as $piece) {
                    $this->addPiece($piece, $key);
                }
            } else {
                $this->addPiece($val);
            }
        }
    }
    
    /**
     * Получение массива фигур на шахматной доске
     *
     * @return array 
     */
    public function getPieces()
    {
        return $this->_pieces;
    }

    /**
     * Установка фигуры на шахматную доску
     *
     * @param Core_Game_Chess_Piece_Abstract|string $piece
     * @param integer                               $color [Optional]
     *
     * @throws Core_Game_Chess_Exception
     * @return Core_Game_Chess_Board
     */
    public function addPiece($piece, $color = null)
    {
        if ($piece instanceof Core_Game_Chess_Piece_Abstract) {
            $piece->setBoard($this);
            $position = $piece->getPosition()->getPosition();
        } elseif (is_string($piece)) {
            $name = $piece[0];
            $position = substr($piece, 1);
            $piece = Core_Game_Chess_Piece::get($name, $position, $this, $color);
        } else {
            throw new Core_Game_Chess_Exception('Invalid piece data format given to add in chess board');
        }
        
        if (null !== $color) {
            $piece->setColor($color);
        }
        
        $horizontalPos = $position[0];
        $verticalPos = $position[1];
        $this->_pieces[$verticalPos][$horizontalPos] = $piece;
        
        //Если фигура король - создаем ссылку на объект фигуры
        if ($piece instanceof Core_Game_Chess_Piece_King) {
            $this->_kings[$piece->getColor()] = $piece;
        }
        
        return $this;
    }
    
    /**
     * Получение объекта фигуры по позиции на шахматной доске
     *
     * @param Core_Game_Chess_Coords_Position|string $position
     * @return Core_Game_Chess_Piece_Abstract|boolean 
     */
    public function getPiece($position)
    {
        if (is_string($position)) {
            $position = new Core_Game_Chess_Coords_Position($position);
        }
        $hPos = $position->getHorizontalPosition();
        $vPos = $position->getVerticalPosition();
        if (isset($this->_pieces[$vPos][$hPos])) {
            return $this->_pieces[$vPos][$hPos];
        }
        
        return false;
    }
    
    /**
     * Метод удаление фигуры с шахматной доски
     *
     * @param Core_Game_Chess_Coords_Position|string $position 
     */
    public function unsetPiece($position)
    {
        if (is_string($position)) {
            $position = new Core_Game_Chess_Coords_Position($position);
        }
        $hPos = $position->getHorizontalPosition();
        $vPos = $position->getVerticalPosition();
        
        unset($this->_pieces[$vPos][$hPos]);
    }
    
    /**
     * Получение ссылок на объекты фигур королей
     *
     * @return array 
     */
    public function getKings()
    {
        return $this->_kings;
    }

    /**
     * Получение ссылки на объект фигуры короля
     *
     * @param integer $color Идентификатор цвета фигуры
     *
     * @throws Core_Game_Chess_Exception
     * @return Core_Game_Chess_Piece_King
     */
    public function getKing($color)
    {
        if (!isset($this->_kings[$color])) {
            throw new Core_Game_Chess_Exception('Undefined king object link in chess board');
        }
        
        return $this->_kings[$color];
    }
    
    /**
     * Установка возможности короткой рокировки
     *
     * @param integer $color Идентификатор цвета фигур
     * @param boolean $canCastling Возможность рокировки
     * @return Core_Game_Chess_Board 
     */
    public function setShortCastling($color, $canCastling)
    {
        $this->_canShortCastling[$color] = $canCastling;
        return $this;
    }
    
    /**
     * Проверка возможности короткой рокировки
     *
     * @param integer $color Идентификатор цвета фигур
     * @return boolean 
     */
    public function canShortCastling($color)
    {
        return $this->_canShortCastling[$color];
    }
    
    /**
     * Установка возможности длинной рокировки
     *
     * @param integer $color Идентификатор цвета фигур
     * @param boolean $canCastling Возможность рокировки
     * @return Core_Game_Chess_Board 
     */
    public function setLongCastling($color, $canCastling)
    {
        $this->_canLongCastling[$color] = $canCastling;
        return $this;
    }
    
    /**
     * Проверка возможности длинной рокировки
     *
     * @param integer $color Идентификатор цвета фигур
     * @return boolean 
     */
    public function canLongCastling($color)
    {
        return $this->_canLongCastling[$color];
    }
    
    /**
     * Установка события в игре
     *
     * @param string $event
     * @return Core_Game_Chess_Board 
     */
    public function setEvent($event)
    {
        $this->_event = $event;
        return $this;
    }
    
    /**
     * Добавление события
     *
     * @param string $event
     * @return Core_Game_Chess_Board 
     */
    public function addEvent($event)
    {
        $events = array();
        if ($this->getEvent() != null) {
            $events = explode('|', $this->getEvent());
        }
        array_push($events, $event);
        $this->setEvent(implode('|', array_unique($events)));
        
        return $this;
    }
    
    /**
     * Получение текущего события в игре
     *
     * @return string 
     */
    public function getEvent()
    {
        return $this->_event;
    }
    
    /**
     * Проверка наличия события в игре (шах, мат, пат, ничья)
     *
     * @return boolean 
     */
    public function isEvent()
    {
        if ($this->_event != null) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Проверка наличия события
     *
     * @param string $event Тип события
     * @return boolean 
     */
    public function hasEvent($event)
    {
        if (null == $this->_event) {
            return false;
        }
        $events = explode('|', $this->getEvent());
        if (in_array($event, $events)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Очистка данных события
     *
     * @param string|null $event
     * @return Core_Game_Chess_Board
     */
    public function clearEvent($event = null)
    {
        if (null === $event) {
            $this->_event = null;
        } elseif(null != $this->_event) {
            $events = explode('|', $this->getEvent());
            $index = array_search($event, $events);
            if (false !== $index) {
                unset($events[$index]);
            }
            $this->setEvent(implode('|', $events));
        }
        return $this;
    }

    /**
     * Установка истории состояния шахматной доски
     *
     * @param array $history
     * @return Core_Game_Chess_Board
     */
    public function setHistory(array $history)
    {
        $this->_history = $history;
        return $this;
    }

    /**
     * Добавление состояня шахматной доски в историю
     *
     * @param Core_Game_Chess_Board|string|null $chessBoard
     * @return Core_Game_Chess_Board
     */
    public function addHistoryItem($chessBoard = null)
    {
        if (null === $chessBoard) {
            $chessBoard = $this;
        }

        if (is_string($chessBoard)) {
            $this->_history[] = $chessBoard;
        } elseif ($chessBoard instanceof Core_Game_Chess_Board) {
            $this->_history[] = $chessBoard->__toString();
        }

        return $this;
    }

    /**
     * Получение истории состояний шахматной доски
     *
     * @return array
     */
    public function getHistory()
    {
        return $this->_history;
    }
    
    /**
     * Установка предыдущего состояния шахматной доски
     *
     * @param string|null $data
     * @return Core_Game_Chess_Board 
     */
    public function setPreviousBoardState($data = null)
    {
        if (null === $data) {
            $data = $this->__toString();
        }
        $this->_previousBoardState = $data;
        return $this;
    }
    
    /**
     * Получение предыдущего состояния шахматной доски
     *
     * @return string
     */
    public function getPreviousBoardState()
    {
        return $this->_previousBoardState;
    }
    
    /**
     * Получение объекта предыдущего состояния шахматной доски
     *
     * @return Core_Game_Chess_Board
     */
    public function getPreviousBoardObject()
    {
        if ($this->getPreviousBoardState() == null) {
            return clone($this);
        }
        
        $board = new self();
        $board->setPieces($this->getPreviousBoardState());
        return $board;
    }

    /**
     * Установка объекта истории анимаций
     *
     * @param Core_Game_Chess_Animation $animation
     *
     * @return Core_Game_Chess_Board
     */
    public function setAnimation(Core_Game_Chess_Animation $animation)
    {
        $this->_animation = $animation;
        return $this;
    }

    /**
     * Получение объекта истории анимаций
     *
     * @return Core_Game_Chess_Animation
     */
    public function getAnimation()
    {
        return $this->_animation;
    }

    /**
     * Проверка наличия перемещений фигур на шахматной доске
     *
     * @param int $color Цвет фигур
     *
     * @return bool
     */
    public function hasMovement($color)
    {
        //Получаем данные доски в виде строки
        $strBoard = $this->__toString();
        //Разбиваем данные фигур белого и черного цвета
        $arrStringPieces = explode(':', $strBoard);
        //Получение данных фигур указанного цвета
        if ($color == Core_Game_Chess_Piece_Abstract::WHITE) {
            //Проверка соответствия расстановке белых фигур начальному положению
            return $arrStringPieces[0] != Core_Game_Chess::START_WHITE_POSITION;
        } else {
            //Проверка соответствия расстановке черных фигур начальному положению
            return $arrStringPieces[1] != Core_Game_Chess::START_BLACK_POSITION;
        }
    }
    
    /**
     * Данные шахматной доски в виде строки
     *
     * @return string 
     */
    public function __toString() 
    {
        $white = array();
        $black = array();
        foreach($this->getPieces() as $piecesLine) {
            foreach($piecesLine as $piece) {
                $str = $piece->getName() 
                       . $piece->getPosition()->getHorizontalPosition()
                       . $piece->getPosition()->getVerticalPosition();
                if ($piece->getColor() == Core_Game_Chess_Piece_Abstract::WHITE) {
                    $white[] = $str;
                } else {
                    $black[] = $str;
                }
            }
        }
        
        return implode(',', $white) . ':' . implode(',', $black);
    }
    
    /**
     * Преобразование строки, описывающей расположение на шахматной доске, в объекты фигур
     *
     * @param string $strPieces Строка списка позиций шахматных фигур
     * @param integer $color Цвет шахматных фигур
     */
    public function fromString($strPieces, $color)
    {
        $arrPieces = explode(',', $strPieces);
        
        foreach($arrPieces as $shortInfo) {
            //Добавление созданной фигуры на шахматную доску
            $this->addPiece($shortInfo, $color);
        }
    }
}