<?php

/**
 * Абстракция фигуры на шахмотной доске
 *
 * @author aleksey
 */
abstract class Core_Game_Chess_Piece_Abstract
{
    
    /**
     * Идентификаторы цветов фигуры
     */
    const WHITE = 0;
    const BLACK = 1;
    
    /**
     * Ссылка на объекта шахматной доски
     *
     * @var Core_Game_Chess_Board
     */
    protected $_board;
    
    /**
     * Сиситемное имя фигуры
     *
     * @var string 
     */
    protected $_name;
    
    /**
     * Идентификатор цвета фигуры
     *
     * @var integer 
     */
    protected $_color;
    
    /**
     * Текущая позиция фигуры
     *
     * @var Core_Game_Chess_Coords_Position
     */
    protected $_position;


    /**
     * __construct
     *
     * @param string $position [Optional] Текущая позиция фигуры
     * @param null $board
     * @param int $color
     * @return Core_Game_Chess_Piece_Abstract
     *
     */
    public function __construct($position = null, $board = null, $color = self::WHITE) 
    {
        if ($position instanceof Core_Game_Chess_Coords_Position) {
            $this->setPosition($position);
        }
        if ($board instanceof Core_Game_Chess_Board) {
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
        $this->setPosition(clone ($this->_position));
        //Привязка данного объекта фигуры с клонированной шахматной доской
        $this->getBoard()->addPiece($this);
    }
    
    /**
     * Установка ссылки на объект шахматной доски
     *
     * @param Core_Game_Chess_Board $board 
     */
    public function setBoard(Core_Game_Chess_Board $board)
    {
        $this->_board = $board;
    }
    
    /**
     * Получение ссылки на объект шахматной доски
     *
     * @return Core_Game_Chess_Board 
     */
    public function getBoard()
    {
        return $this->_board;
    }
    
    /**
     * Получение имени фигуры
     *
     * @return string 
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Установка цвета фигуры
     *
     * @param integer $color 
     */
    public function setColor($color)
    {
        $this->_color = $color;
    }
    
    /**
     * Получение цвета фигуры
     *
     * @return integer
     */
    public function getColor()
    {
        return $this->_color;
    }
    
    /**
     * Установка текущей позиции фигуры
     *
     * @param Core_Game_Chess_Coords_Position $position 
     */
    public function setPosition(Core_Game_Chess_Coords_Position $position)
    {
        $this->_position = $position;
    }
    
    /**
     * Получение текущей позиции фигуры
     *
     * @return Core_Game_Chess_Coords_Position
     */
    public function getPosition() 
    {
        return $this->_position;
    }

    /**
     * Перемещение фигуры на указанную позицию
     *
     * @param Core_Game_Chess_Coords_Position $position Объект позиции перемещения фигуры
     * @param int|null                        $command  Текущий идентификатор команды обновления данных игры
     *
     * @return bool
     */
    public function move(Core_Game_Chess_Coords_Position $position, $command = null)
    {
        if (!$this->valid($position)) {
            return false;
        }

        //Запоминаем текущую позицию фигуры
        $oldPosition = $this->getPosition()->getPosition();
        //Освобождение текущего места фигуры на щахматной доске
        $this->getBoard()->unsetPiece($this->getPosition());
        //Установка новой позиции фигуры
        $this->setPosition($position);
        //Установка фигуры на шахматной доске
        $this->getBoard()->addPiece($this);
        //Добавление данных перемещения в историю анимации
        if (null !== $command) {
            $this->getBoard()->getAnimation()->addAction(
                $command,
                Core_Game_Chess_Animation::MOVE,
                $oldPosition,
                $position->getPosition()
            );
        }
        
        return true;
    }

    /**
     * Проверка возможности перемещения фигуры на указанную позицию
     *
     * @abstract
     * @param Core_Game_Chess_Coords_Position $position
     * @return boolean
     */
    abstract public function valid(Core_Game_Chess_Coords_Position $position);

    /**
     * Проверка наличия позиции на шахматной доске для валидного перемещения фигуры (для определения ПАТА)
     *
     * @abstract
     * @return boolean
     */
    abstract public function hasValid();

    /**
     * Проверка возможности перемещения фигуры на текущей доске в указанную позицию
     *
     * @param Core_Game_Chess_Coords_Position $position
     * @param bool $withFreePosition
     * @return boolean
     */
    protected function _validMove(Core_Game_Chess_Coords_Position $position, $withFreePosition = false) 
    {
        $oldX = $this->getPosition()->getHorizontalIndex();
        $oldY = $this->getPosition()->getVerticalIndex();
        $newX = $position->getHorizontalIndex();
        $newY = $position->getVerticalIndex();
        
        //Направление шага для прохода по горизонтали
        if ($oldX == $newX) {
            $stepX = 0;
        } else {
            $stepX = ($oldX < $newX) ? 1 : -1;
        }
        //Направление шага для прохода по вертикали
        if ($oldY == $newY) {
            $stepY = 0;
        } else {
            $stepY = ($oldY < $newY) ? 1 : -1;
        }
        //Текущие значения координат при проходе по движению фигуры
        $x = $oldX + $stepX;
        $y = $oldY + $stepY;
        //Проход по пути передвижения фигуры
        while($x != $newX || $y != $newY) {
            //Позиция на доске
            $tempPos = new Core_Game_Chess_Coords_Position(array($x, $y));
            //Проверка наличия фигуры на пути
            if ($this->getBoard()->getPiece($tempPos)) {
                return false;
            }
            //Переход на следующую клетку
            $x += $stepX;
            $y += $stepY;
        }
        
        //Проверка клетки в позиции перемещения фигуры, 
        //если там нет фигуры либо чужая фигура (если нет флага пустой клетки перемещения withFreePosition) - ход разрешен, иначе нет
        $tempPos = new Core_Game_Chess_Coords_Position(array($newX, $newY));
        $piece = $this->getBoard()->getPiece($tempPos);
        if ($piece && ($withFreePosition || $piece->getColor() == $this->getColor())) {
            return false;
        }

        return true;
    }

    /**
     * Проверка шаха собственному королю после перемещения фигуры
     *
     * @param Core_Game_Chess_Coords_Position $position
     * @return bool
     */
    protected function _isCheckIfMove(Core_Game_Chess_Coords_Position $position)
    {
        //Коприруем объект фигуры и данные шахматной доски
        $piece = clone ($this);
        //Перемещаем фигуру в укзанную позицию
        if ($piece->move($position)) {
            //Проверка шаха
            $king = $piece->getBoard()->getKing($piece->getColor());
            $check = new Core_Game_Chess_Control_Check($king);
            if ($check->check()) {
                //Королю угрожает шах
                return true;
            }
        }

        //Нет шаха
        return false;
    }
    
}