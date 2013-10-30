<?php

/**
 * Класс фигуры пешки на шахмотной доске
 *
 * @author aleksey
 */
class Core_Game_Chess_Piece_Pawn extends Core_Game_Chess_Piece_Abstract
{
    
    /**
     * Системное имя фигуры
     *
     * @var string 
     */
    protected $_name = Core_Game_Chess_Piece::PAWN;
    
    /**
     * Флаг взятия на проходе
     *
     * @var boolean 
     */
    private $_isEnPassant = false;


    /**
     * Перемещение фигуры на указанную позицию
     *
     * @param Core_Game_Chess_Coords_Position $position Объект позиции перемещения фигуры
     * @param int                             $command  Текущий идентификатор команды обновления данных игры
     *
     * @return bool
     */
    public function move(Core_Game_Chess_Coords_Position $position, $command = null)
    {
        //Сброс флага взятия на проходе
        $this->_isEnPassant = false;
        
        //Перемещение фигуры
        $result = parent::move($position, $command);
        
        //Проверка взятия на проходе
        if ($this->_isEnPassant) {
            //Получаем позицию пешки противника
            $pos = clone ($position);
            if ($this->getColor() == self::WHITE) {
                $pos->setVerticalIndex($position->getVerticalIndex() - 1);
            } else {
                $pos->setVerticalIndex($position->getVerticalIndex() + 1);
            }
            //Убиваем пешку противника
            $this->getBoard()->unsetPiece($pos);
            //Запись действия удаления пешки в историю анимаций
            if (null !== $command) {
                $this->getBoard()->getAnimation()->addAction(
                    $command,
                    Core_Game_Chess_Animation::BEAT_OFF,
                    $pos->getPosition()
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Валидация перемещения фигуры
     *
     * @param Core_Game_Chess_Coords_Position $position
     * @return boolean 
     */
    public function valid(Core_Game_Chess_Coords_Position $position) 
    {   
        //Координаты текущего положения
        $oldX = $this->getPosition()->getHorizontalIndex();
        $oldY = $this->getPosition()->getVerticalIndex();
        //Координаты позиции перемещения
        $x = $position->getHorizontalIndex();
        $y = $position->getVerticalIndex();
        //Проверка выхода за пределы шахматной доски
        $n = count(Core_Game_Chess_Coords_Position::$_horizontal) - 1;
        if ($x < 0 || $x > $n || $y < 0 || $y > $n) {
            return false;
        }
        //Направление хода пешки
        $direct = $this->getColor() == self::WHITE ? 1 : -1;
        //Проверка хода только по вертикали
        if ($oldX == $x) {
            //Ход на одну клетку
            if ($oldY + (1 * $direct) == $y) {
                return $this->_validMove($position, true);
            } 
            //Ход на две клетки с начальной позиции
            $isStart = $this->getColor() == self::WHITE ? 
                    ($oldY == 1) : 
                    ($oldY == count(Core_Game_Chess_Coords_Position::$_vertical) - 2);
            if ($isStart && $oldY + (2 * $direct) == $y) {
                return $this->_validMove($position, true);
            }
        }
        
        //Проверка хода по горизонтали
        $diffX = abs($oldX - $x);
        if ($diffX == 1 && $oldY + (1 * $direct) == $y) {
            //Проверка наличия фигуры противника на позиции перехода
            if ($this->getBoard()->getPiece($position)) {
                return $this->_validMove($position);
            }
            //Проверка взятия на проходе
            $tempY = $y + (-1 * $direct);
            $isStart = $this->getColor() == self::BLACK ? 
                    ($tempY == (3)) : 
                    ($tempY == count(Core_Game_Chess_Coords_Position::$_vertical) - (4));
            if ($isStart) {
                //Получаем фигуру за нашей пешкой
                $tempPosition = new Core_Game_Chess_Coords_Position(array($x, $tempY));
                $piece = $this->getBoard()->getPiece($tempPosition);
                //Проверка наличия чужой пешки
                if ($piece && 
                        $piece->getName() == $this->getName() &&
                        $piece->getColor() != $this->getColor()) {
                    //Проверяем, переместилась ли пешка соперника на данную позицию в предыдущем ходе.
                    $previousBoard = $this->getBoard()->getPreviousBoardObject();
                    if ($previousBoard->getPiece($piece->getPosition())) {
                        //Перед последним ходом соперника, пешка, которую пытаемся сбить на проходе, уже стаяла на данной позиции - бить нельзя
                        return false;
                    }
                    //Установка флага взятия на проходе
                    $this->_isEnPassant = true;
                    return $this->_validMove($position);
                }
            }
        }
        
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
        
        //Направление движения пешки
        if ($this->getColor() == Core_Game_Chess_Piece_Abstract::WHITE) {
            $direct = 1;
        } else {
            $direct = -1;
        }
        
        //Проверка возможности перемещения
        $j = $y + (1 * $direct);
        for($i = $x -1; $i <= $x + 1; $i++) {
            if ($i < 0 || $i > count(Core_Game_Chess_Coords_Position::$_horizontal) - 1) {
                continue;
            }
            $position = new Core_Game_Chess_Coords_Position(array($i, $j));
            if ($this->valid($position) && !$this->_isCheckIfMove($position)) {
                //Перемещение возможно
                return true;
            }
        }
        
        //Нет доступных позиций для перемещения фигуры
        return false;
    }

    /**
     * Проход пешки. Изменение пешки на другую, выбранную фигуру
     *
     * @param string $pieceName
     * @param int    $command
     *
     * @return bool|Core_Game_Chess_Piece_Abstract
     */
    public function promotion($pieceName, $command)
    {
        //Проверка прохода пешки
        if (!$this->isPromotion()) {
            return false;
        }
        
        //Создание фигуры на которую необходимо заменить пешку
        $newPiece = Core_Game_Chess_Piece::get($pieceName, $this->getPosition()->getPosition());
        //Удаляем пешку с шахматной доски
        $this->getBoard()->unsetPiece($this->getPosition());
        //Установка новой фигуры на позицию пешки
        $this->getBoard()->addPiece($newPiece, $this->getColor());
        //Обнуляем историю ходов
        $this->getBoard()->setHistory(array());
        //Записваем действие в историю анимаций
        $this->getBoard()->getAnimation()->addAction(
            $command,
            Core_Game_Chess_Animation::PROMOTION,
            $this->getPosition()->getPosition(),
            null,
            $newPiece->getName()
        );
        
        return $newPiece;
    }
    
    /**
     * Проверка прохода (превращения) пешки. 
     * Достижение пешки самой дальней горизонтали от своей исходной позиции
     *
     * @return bool 
     */
    public function isPromotion()
    {
        if ($this->getColor() == Core_Game_Chess_Piece_Abstract::WHITE) {
            $verticalPosition = 8;
        } else {
            $verticalPosition = 1;
        }
        if ($this->getPosition()->getVerticalPosition() == $verticalPosition) {
            return true;
        }
        
        return false;
    }
    
}