<?php

/**
 * Description of Board
 *
 * @author aleksey
 */
class Core_Game_Checkers_Board
{
    
    /**
     * Массив объектов шашек
     *
     * @var array 
     */
    protected $_pieces = array();
    
    /**
     * Количество ходов без перемещения обычных шашек
     *
     * @var integer
     */
    protected $_history = 0;

    /**
     * Список битых шашек за последний ход
     *
     * @var Core_Game_Checkers_Piece[]
     */
    protected $_lastKilledPieces = array();
    
    
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
                     '_history');
    }
    
    /**
     * Восстановление данных объекта при unserialize
     */
    public function __wakeup() 
    {
        //Изменение типа поля списка шашек
        $strPieces = $this->getPieces();
        $this->_pieces = array();
        //Восстановление данных доски из строки
        $arrPieces = explode(':', $strPieces);
        //Расставление белых шашек на доску
        $this->fromString($arrPieces[0], Core_Game_Checkers_Piece::WHITE);
        //Расставление черных фигур на доску
        $this->fromString($arrPieces[1], Core_Game_Checkers_Piece::BLACK);
    }
    
    /**
     * Клонирование объекта
     */
    public function __clone()
    {
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
     * Получение списка объектов шашек
     *
     * @return array
     */
    public function getPieces()
    {
        return $this->_pieces;
    }
    
    /**
     * Добавление объекта шашки на шахматную доску
     *
     * @param Core_Game_Checkers_Piece|Core_Game_Chess_Coords_Position|array|string $piece
     * @param integer $color
     * @return Core_Game_Checkers_Board 
     */
    public function addPiece($piece, $color = null)
    {
        if (is_string($piece) || is_array($piece) || 
                $piece instanceof Core_Game_Chess_Coords_Position) {
            $piece = new Core_Game_Checkers_Piece($piece);
        } elseif (!$piece instanceof Core_Game_Checkers_Piece) {
            throw new Core_Game_Checkers_Exception('Invalid piece data format given to add in checkers board');
        }
        
        //Установка своиств шашки
        $piece->setBoard($this);
        if (null !== $color) {
            $piece->setColor($color);
        }
        
        //Добавление объекта шашки в массив
        $verticalPos = $piece->getPosition()->getVerticalPosition();
        $horizontalPos = $piece->getPosition()->getHorizontalPosition();
        $this->_pieces[$verticalPos][$horizontalPos] = $piece;
        
        return $this;
    }
    
    /**
     * Получение объекта шашки
     *
     * @param Core_Game_Chess_Coords_Position|string $position
     * @return Core_Game_Checkers_Piece
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
     * Удаление шашки с игровой доски
     *
     * @param Core_Game_Chess_Coords_Position|string $position
     * @return bool 
     */
    public function unsetPiece($position)
    {
        if (is_string($position)) {
            $position = new Core_Game_Chess_Coords_Position($position);
        }
        $hPos = $position->getHorizontalPosition();
        $vPos = $position->getVerticalPosition();
        if (isset($this->_pieces[$vPos][$hPos])) {
            unset($this->_pieces[$vPos][$hPos]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Установка количества ходов без перемещения обычных шашек
     *
     * @param integer $history
     * @return Core_Game_Checkers_Board 
     */
    public function setHistory($history)
    {
        $this->_history = $history;
        return $this;
    }
    
    /**
     * Получение количества ходов без перемещения обычных шашек
     *
     * @return integer 
     */
    public function getHistory()
    {
        return $this->_history;
    }
    
    /**
     * Увеличение количества ходов без перемещения обычных шашек
     *
     * @return Core_Game_Checkers_Board 
     */
    public function incHistory()
    {
        $this->_history ++;
        
        return $this;
    }
    
    /**
     * Сброс количества ходов без перемещения обычных шашек
     *
     * @return Core_Game_Checkers_Board 
     */
    public function clearHistory()
    {
        $this->_history = 0;
        
        return $this;
    }
    
    /**
     * Проверка наличия возможности перемещения шашки у игрока
     *
     * @param integer $color Идентификатор цвета шашки игрока
     * @return bool 
     */
    public function hasMove($color)
    {
        foreach($this->getPieces() as $line) {
            foreach($line as $piece) {
                if ($piece->getColor() == $color && $piece->hasValid()) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Проверка возможности игрока бить шашку соперника
     *
     * @param integer $color Идентификатор цвета шашек игрока
     * @return bool 
     */
    public function hasKill($color)
    {
        foreach($this->getPieces() as $line) {
            foreach($line as $piece) {
                if ($piece->getColor() == $color && $piece->hasKill()) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Добавление ссылки на удаленную шашку в список битых шашек
     *
     * @param Core_Game_Checkers_Piece $piece
     */
    public function addKillPiece(Core_Game_Checkers_Piece $piece)
    {
        $this->_lastKilledPieces[] = $piece;
    }

    /**
     * Получение списка битых шашек в последнем ходе
     *
     * @return array|Core_Game_Checkers_Piece[]
     */
    public function getLastKilledPieces()
    {
        return $this->_lastKilledPieces;
    }
    
    /**
     * Получение списка битых шашек на доске
     *
     * @return array 
     */
    public function getKilledPieces()
    {
        $killed = array();
        foreach($this->getPieces() as $line) {
            foreach($line as $piece) {
                if ($piece->isKilled()) {
                    $killed[] = $piece;
                }
            }
        }
        
        return $killed;
    }
    
    /**
     * Проверка ничьию. Если за 15 ходов (в сумму - 30 перемещений) не передвинута или не убита ни одна шашка - ничья
     *
     * @return true 
     */
    public function isDraw()
    {
        //Если в истории 30 записей, значит обычные шашки перемещены не были
        if ($this->getHistory() == 30) {
            return true;
        }
        
        return false;
    }

    /**
     * Проверка наличия перемещений шашек определенного цвета на шахматной доске
     *
     * @param int $color Цвет шашек
     *
     * @return bool
     */
    public function hasMovement($color)
    {
        //Получение данных расположения шашек в виде строки
        $strBoard = $this->__toString();
        //Разделение данныз белых и черных шашек
        $arrStringBoard = explode(':', $strBoard);
        //Проверка изменения поизиций шашек
        if ($color == Core_Game_Checkers_Piece::WHITE) {
            //Проверка соответствия позиций белых шашек начальной позиции на доске
            return $arrStringBoard[0] != Core_Game_Checkers::START_WHITE_POSITION;
        } else {
            //Проверка соответствия позиций черных шашек начальной позиции на доске
            return $arrStringBoard[1] != Core_Game_Checkers::START_BLACK_POSITION;
        }
    }
    
    /**
     * Объект доски в виде строки
     *
     * @return string 
     */
    public function __toString() 
    {
        $white = array();
        $black = array();
        foreach($this->getPieces() as $line) {
            foreach($line as $piece) {
                if ($piece->getColor() == Core_Game_Checkers_Piece::WHITE) {
                    $white[] = $piece->__toString();
                } else {
                    $black[] = $piece->__toString();
                }
            }
        }
        
        return implode(',', $white) . ':' . implode(',', $black);
    }

    /**
     * Преобразование строки в данные доски
     *
     * @param string  $strPieces
     * @param integer $color
     *
     * @throws Core_Game_Checkers_Exception
     */
    public function fromString($strPieces, $color)
    {
        if (!is_string($strPieces)) {
            throw new Core_Game_Checkers_Exception('Invalid data given to generate board');
        }
        
        foreach(explode(',', $strPieces) as $info) {
            $position = substr($info, 0, 2);
            $piece = new Core_Game_Checkers_Piece($position, null, $color);
            if (strstr($info, '@')) {
                $piece->setKing();
            }
            $this->addPiece($piece);
        }
    }
    
}