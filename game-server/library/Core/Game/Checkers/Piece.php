<?php

/**
 * Description of Piece
 *
 * @author aleksey
 */
class Core_Game_Checkers_Piece 
{
    
    /**
     * Коды цветов шашек
     */
    const WHITE = 0;
    const BLACK = 1;
    
    /**
     * Позиция шашки на шахматной доске
     *
     * @var Core_Game_Chess_Coords_Position
     */
    protected $_position;
    
    /**
     * Цвет шашки
     *
     * @var integer 
     */
    protected $_color;
    
    /**
     * Объект доски
     *
     * @var Core_Game_Checkers_Board 
     */
    protected $_board;
    
    /**
     * Флаг "в дамках"
     *
     * @var bool 
     */
    protected $_isKing = false;
    
    /**
     * Флаг "битая шашка"
     *
     * @var bool 
     */
    protected $_isKilled = false;
    
    /**
     * Битая шашка противника
     *
     * @var Core_Game_Checkers_Piece 
     */
    private $_opponentKilledPiece = null;
    
    
    /**
     * __construct
     *
     * @param Core_Game_Chess_Coords_Position|string|array $position
     * @param Core_Game_Checkers_Board|null $board
     * @param integer $color
     * @param bool $isKing 
     */
    public function __construct($position, $board = null, $color = self::WHITE, $isKing = false)
    {
        $this->setPosition($position)
             ->setColor($color)
             ->setKing($isKing);
        
        if (null !== $board) {
            $this->setBoard($board);
        }
    }
    
    /**
     * Клонирование объекта
     */
    public function __clone()
    {
        //Клонирование объектов по ссылкам
        $this->setBoard(clone($this->_board));
        $this->setPosition(clone($this->_position));
        //Привязка данного объекта шашки с клонированной шахматной доской
        $this->getBoard()->addPiece($this);
    }
    
    /**
     * Установка позиции шашки на шахматной доске
     *
     * @param Core_Game_Chess_Coords_Position|string|array $position
     * @return Core_Game_Checkers_Piece 
     */
    public function setPosition($position)
    {
        if (!$position instanceof Core_Game_Chess_Coords_Position) {
            $position = new Core_Game_Chess_Coords_Position($position);
        }
        
        $this->_position = $position;
        return $this;
    }
    
    /**
     * Получение позиции шашки на шахматной доске
     *
     * @return Core_Game_Chess_Coords_Position 
     */
    public function getPosition()
    {
        return $this->_position;
    }
    
    /**
     * Установка объекта шахматной доски
     *
     * @param Core_Game_Checkers_Board $board
     * @return Core_Game_Checkers_Piece 
     */
    public function setBoard(Core_Game_Checkers_Board $board)
    {
        $this->_board = $board;
        return $this;
    }
    
    /**
     * Получение объекта шахматной доски
     *
     * @return Core_Game_Checkers_Board 
     */
    public function getBoard()
    {
        return $this->_board;
    }
    
    /**
     * Установка цвета шашки
     *
     * @param integer $color
     * @return Core_Game_Checkers_Piece 
     */
    public function setColor($color)
    {
        $this->_color = $color;
        return $this;
    }
    
    /**
     * Получение цвета шашки
     *
     * @return integer
     */
    public function getColor()
    {
        return $this->_color;
    }
    
    /**
     * Установка флага "в дамках"
     *
     * @param bool $isKing
     * @return Core_Game_Checkers_Piece 
     */
    public function setKing($isKing = true)
    {
        $this->_isKing = $isKing;
        return $this;
    }
    
    /**
     * Проверка флага "в дамках"
     *
     * @return bool 
     */
    public function isKing()
    {
        return $this->_isKing;
    }
    
    /**
     * Установка влага "битая шашка"
     *
     * @param bool $isKilled
     * @return Core_Game_Checkers_Piece 
     */
    public function setKill($isKilled = true)
    {
        $this->_isKilled = $isKilled;
        return $this;
    }
    
    /**
     * Проверка флага "битая шашка"
     *
     * @return bool 
     */
    public function isKilled()
    {
        return $this->_isKilled;
    }
    
    /**
     * Проверка валидности перемещения шашки
     *
     * @param Core_Game_Chess_Coords_Position $position
     * @return bool 
     */
    public function valid(Core_Game_Chess_Coords_Position $position)
    {
        //Проверка клетки в указанной позиции, должна быть пустой
        if ($this->getBoard()->getPiece($position)) {
            return false;
        }
        
        $curPos = $this->getPosition();
        //Проверка перемещения по диагонали (сумма/разница текущих координат должна равнятся сумме/разнице новых координат)
        $curSum = $curPos->getHorizontalIndex() + $curPos->getVerticalIndex();
        $newSum = $position->getHorizontalIndex() + $position->getVerticalIndex();
        if ($curSum != $newSum) {
            $curDiff = $curPos->getHorizontalIndex() - $curPos->getVerticalIndex();
            $newDiff = $position->getHorizontalIndex() - $position->getVerticalIndex();
            if ($curDiff != $newDiff) {
                //Перемещение не по горизонтали
                return false;
            }
        }
        
        //Количество клеток, пройденых за ход
        $diff = abs($curPos->getHorizontalIndex() - $position->getHorizontalIndex());
        
        //Проверка прыжка обычной шашки больше чем через одну поицию
        if (!$this->isKing() && $diff > 2) {
            return false;
        }
        
        //Проверка перехода на одну клетку (разница текущей и новой позиции = 1)
        if ($diff == 1) {
            //Проверка возможности бить шашку противника
            if ($this->getBoard()->hasKill($this->getColor())) {
                //Необходимо бить, ход неправильный
                return false;
            } else {
                
                //Проверка хода назад обычной шашки
                if (!$this->isKing()) {
                    //Индекс текущей позиции по вертикали
                    $curY = $this->getPosition()->getVerticalIndex();
                    //Индекс позиции перемещения по вертикали
                    $nextY = $position->getVerticalIndex();
                    //Проверерка хода назад
                    if ($this->getColor() == Core_Game_Checkers_Piece::WHITE && 
                            $curY > $nextY) {
                        return false;
                    } elseif ($this->getColor() == Core_Game_Checkers_Piece::BLACK &&
                            $curY < $nextY) {
                        return false;
                    }
                }
                
                return true;
            }
        }
        
        //Проход по пути премещения шашки, получаем массив шашек на пути
        $piecesOnWay = $this->_getPiecesOnWay($position);
        if (count($piecesOnWay) > 1) {
            //На пути перемещения больше одной шашки
            return false;
        }
        //Проверка наличия своих шашек на пути перемещения
        if (isset($piecesOnWay[0]) && 
                $piecesOnWay[0]->getColor() == $this->getColor()) {
            return false;
        }
        //Проверка валидности хода обычной бьющей шашки
        if (!$this->isKing() && count($piecesOnWay) == 0) {
            //Шашка перепрыгнула пустое поле
            return false;
        }
        //Проверка валидности хода шашки в дамках
        elseif ($this->isKing() && count($piecesOnWay) == 0) {
            //Проверка возможности бить шашку противника
            if ($this->getBoard()->hasKill($this->getColor())) {
                //Необходимо бить шашку противника, ход неправильный
                return false;
            }
        }
        
        //Проверка наличия битой шашки противника
        if (isset($piecesOnWay[0])) {
            //Запоминаем битую шашку
            $this->_setKilledPiece($piecesOnWay[0]);
        }
        
        return true;
    }
    
    /**
     * Проверка наличия валидного хода у шашки
     *
     * @return bool 
     */
    public function hasValid()
    {
        //Границы игрового поля
        $maxHorizontalIndex = count(Core_Game_Chess_Coords_Position::$_horizontal) - 1;
        $maxVerticalIndex = count(Core_Game_Chess_Coords_Position::$_vertical) - 1;
        //Текущая позиция шашки
        $x = $this->getPosition()->getHorizontalIndex();
        $y = $this->getPosition()->getVerticalIndex();
        //Проверка возможности хода на одну позицию во всех направлениях по горизонтали
        for($i = -1; $i <= 1; $i++) {
            if ($i == 0 || $x + $i < 0 || $x + $i > $maxHorizontalIndex)
                continue;
            for($j = -1; $j <= 1; $j++) {
                if ($j == 0 || $y + $j < 0 || $y + $j > $maxVerticalIndex) 
                    continue;
                //Объект найденной позиции
                $position = new Core_Game_Chess_Coords_Position(array($x+$i, $y+$j));
                //Проверка возможности хода
                if ($this->valid($position)) {
                    //Поле свободно, перемещение возможно
                    return true;
                }
            }
        }
        //Проверка возможности бить шашку противника
        if ($this->hasKill()) {
            return true;
        }
        
        //Нет возможности хода шашкой
        return false;
    }
    
    /**
     * Перемещение шашки
     *
     * @param Core_Game_Chess_Coords_Position $position 
     * @param bool $isChain [optional] Ход по цепочке (нужно только бить)
     * @return bool
     */
    public function move(Core_Game_Chess_Coords_Position $position, $isChain = false)
    {
        //Сброс объекта битой шашки за предыдущее перемещение
        $this->_clearKilledPiece();
        //Проверка валидности хода
        if (!$this->valid($position)) {
            return false;
        }
        
        //Если ход по цепочке и нет сбитой шашки противника, ход не верный
        if ($isChain && !$this->_isKilledPiece()) {
            return false;
        }
        
        //Удаление шашки с доски
        $this->getBoard()->unsetPiece($this->getPosition());
        //Перемещение шашки
        $this->setPosition($position);
        $this->getBoard()->addPiece($this);
        //Если есть сбитая шашка противника, устанавливаем ей флаг "битая шашка"
        if ($this->_isKilledPiece()) {
            //Проверяем не была ли сбита шашка в предыдущих ходах цепочки
            if ($this->_getKilledPiece()->isKilled()) {
                //Два раза одну шашку бить нельзя
                return false;
            }
            //Установка флага для бтой шашки
            $this->_getKilledPiece()->setKill();
            //Добавление в список битых шашек за текущий ход (для корректного анимирования)
            $this->getBoard()->addKillPiece($this->_getKilledPiece());
        }
        
        //Проверка хода без перемещения обычных шашек
        if (!$this->_isKilledPiece() && $this->isKing()) {
            //Увеличиваем количество ходов без перемещения обычных шашек
            $this->getBoard()->incHistory();
        } else {
            //Сброс истории ходов без перемещения обычных шашек
            $this->getBoard()->clearHistory();
        }
        
        //Проверяем достижения противоположной границы доски (хода "в дамки")
        $vertical = $this->getPosition()->getVerticalIndex();
        if ($this->getColor() == Core_Game_Checkers_Piece::WHITE &&
                !isset(Core_Game_Chess_Coords_Position::$_vertical[$vertical + 1])) {
            $this->setKing();
        } elseif ($this->getColor() == Core_Game_Checkers_Piece::BLACK &&
                !isset(Core_Game_Chess_Coords_Position::$_vertical[$vertical - 1])) {
            $this->setKing();
        }
        
        return true;
    }
    
    /**
     * Проверка возможности шашки бить шашку соперника
     *
     * @return bool 
     */
    public function hasKill()
    {
        //Сбитая шашка бить не может
        if ($this->isKilled()) {
            return false;
        }
        
        //Текущая позиция шашки
        $position = $this->getPosition();
        //Проверка возможности бить соперника
        return $this->_hasOpponentForKill($position);
    }
    
    /**
     * Проверка наличия шашки противника для сбития по направлению хода дамки от последней сбитой шашки
     *
     * @return bool 
     */
    public function hasPieceForKillInLastMoveWay()
    {
        //Шашка должна быть в дамках
        if (!$this->isKing()) {
            return false;
        }
        
        //За последнее перемещение должна быть сбита шашка
        if (!$this->_isKilledPiece()) {
            return false;
        }
        
        /**
         * Проходим все клетки по направлению от сбитой шашки до текущей позиции (и дальше по направлению)
         */
        $startX = $this->_getKilledPiece()->getPosition()->getHorizontalIndex();
        $startY = $this->_getKilledPiece()->getPosition()->getVerticalIndex();
        $currX = $this->getPosition()->getHorizontalIndex();
        $currY = $this->getPosition()->getVerticalIndex();
        $directX = $currX > $startX ? 1 : -1;
        $directY = $currY > $startY ? 1 : -1;
        $maxX = count(Core_Game_Chess_Coords_Position::$_horizontal) - 1;
        $maxY = count(Core_Game_Chess_Coords_Position::$_vertical) - 1;
        //Перемещение на одну клетку
        $startX += $directX;
        $startY += $directY;
        while($startX <= $maxX && $startX >= 0 && $startY <= $maxY && $startY >= 0) {
            //Текущая позиция
            $position = new Core_Game_Chess_Coords_Position(array($startX, $startY));
            //Проверка cоответствия с текущей позицией шашки
            if ($position->getPosition() == $this->getPosition()->getPosition()) {
                //Переход на следующую клетку
                $startX += $directX;
                $startY += $directY;
                continue;
            }
            //Проверка наличия шашки на пути
            if ($this->getBoard()->getPiece($position)) {
                break;
            }
            //Проверка наличия шашки для сбития
            if ($this->_hasOpponentForKill($position)) {
                return true;
            }
            //Переход на следующую клетку
            $startX += $directX;
            $startY += $directY;
        }
        
        return false;
    }
    
    /**
     * Объект шашки в виде строки
     *
     * @return string 
     */
    public function __toString() 
    {
        $str = $this->getPosition()->getPosition();
        if ($this->isKing()) {
            $str .= '@';
        }
        return $str;
    }
    
    /**
     * Проверка наличия на пути перемещения, шашки противника для сбития
     *
     * @param Core_Game_Chess_Coords_Position $position
     * @param array|null $directs
     * @return bool 
     */
    protected function _hasOpponentForKill(Core_Game_Chess_Coords_Position $position, array $directs = null)
    {
        //Все возможные направления
        if (null === $directs) {
            $directs = array(array(-1, 1),array(-1, -1),array(1, -1),array(1, 1));
        }
        
        //Проверка наличия противника в каждом направлнии
        foreach($directs as $direct) {
            $posX = $position->getHorizontalIndex() + $direct[0];
            $posY = $position->getVerticalIndex() + $direct[1];
            //Следующая позиция по направлению
            $nextPos1 = $this->_getPosition($posX, $posY);
            //Следующая позиция по направлению
            $nextPos2 = $this->_getPosition($posX + $direct[0], $posY + $direct[1]);
            //Проверка наличия позиций на шахматной доске
            if (!$nextPos1 || !$nextPos2) {
                continue;
            }
            //Проверка наличия шашки на соседнем поле
            if (($piece = $this->getBoard()->getPiece($nextPos1))) {
                //Проверка цвета шашки
                if ($this->getColor() == $piece->getColor()) {
                    continue;
                }
                //Не сбита ли найденая шашка
                if ($piece->isKilled()) {
                    continue;
                }
                //Проверка наличия пустой клетки за найденой шашкой оппонента
                if (!$this->getBoard()->getPiece($nextPos2)) {
                    return true;
                }
            } elseif ($this->isKing() && !$piece) {
                //Шашка "в дамках", идем дальше по направлению
                if ($this->_hasOpponentForKill($nextPos1, array($direct))) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Получение списка шашек на пути перемещения
     *
     * @param Core_Game_Chess_Coords_Position $position
     * @return array|bool 
     */
    protected function _getPiecesOnWay(Core_Game_Chess_Coords_Position $position)
    {
        //Определение направления перемещения по горизонтали/вертикали
        $curPos = $this->getPosition();
        if ($curPos->getHorizontalIndex() < $position->getHorizontalIndex()) {
            $hDirect = 1;
        } else {
            $hDirect = -1;
        }
        if ($curPos->getVerticalIndex() < $position->getVerticalIndex()) {
            $vDirect = 1;
        } else {
            $vDirect = -1;
        }
        
        //Массив шашек на пути
        $pieces = array();
        
        //Проход по пути перемещения
        $hPos = $curPos->getHorizontalIndex();
        $vPos = $curPos->getVerticalIndex();
        while($hPos != $position->getHorizontalIndex() && 
                $vPos != $position->getVerticalIndex()) {
            $hPos += $hDirect;
            $vPos += $vDirect;
            if (!($newPos = $this->_getPosition($hPos, $vPos))) {
                break;
            }
            if (($piece = $this->getBoard()->getPiece($newPos))) {
                $pieces[] = $piece;
            }
            
        }
        
        return $pieces;
    }
    
    /**
     * Установка объекта сбитой шашки соперника при последнем перемещении
     *
     * @param Core_Game_Checkers_Piece $piece
     * @return Core_Game_Checkers_Piece 
     */
    private function _setKilledPiece(Core_Game_Checkers_Piece $piece)
    {
        $this->_opponentKilledPiece = $piece;
        return $this;
    }
    
    /**
     * Получение объекта сбитой шашки соперника при последнем перемещении
     *
     * @return Core_Game_Checkers_Piece|null 
     */
    private function _getKilledPiece()
    {
        return $this->_opponentKilledPiece;
    }
    
    /**
     * Проверка наличия сбитой за посленее перемещение шашки соперника
     *
     * @return bool
     */
    private function _isKilledPiece()
    {
        return null !== $this->_opponentKilledPiece;
    }
    
    /**
     * Очистка объекта сбитой шашки соперника за последнее перемещение
     */
    private function _clearKilledPiece()
    {
        $this->_opponentKilledPiece = null;
    }
    
    /**
     * Получение объекта позиции на шахматной доске
     *
     * @param integer $x
     * @param integer $y
     * @return Core_Game_Chess_Coords_Position|bool
     */
    private function _getPosition($x, $y)
    {
        if (!isset(Core_Game_Chess_Coords_Position::$_horizontal[$x])) {
            return false;
        }
        if (!isset(Core_Game_Chess_Coords_Position::$_vertical[$y])) {
            return false;
        }
        
        return new Core_Game_Chess_Coords_Position(array($x, $y));
    }
}