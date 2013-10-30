<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 18.06.12
 * Time: 11:03
 *
 * Класс описывающий доску игры Нарды
 */
class Core_Game_Backgammon_Board
{

    /**
     * Цвета шашек на игровой доске
     */
    const WHITE_PIECE = 0;
    const BLACK_PIECE = 1;

    /**
     * Количество ячеек на игровой доске
     */
    const BOARD_CELL_COUNT = 24;

    /**
     * Количество шашек пользователя
     */
    const PLAYER_PIECE_COUNT = 15;


    /**
     * Данные расположения шашек на игровой доске
     *
     * @var array
     */
    protected $_pieces = array();

    /**
     * Значения игральных костей
     *
     * @var Core_Game_Backgammon_Dice
     */
    protected $_dice;

    /**
     * Флаг перемещения шашки с начальной позиции в рамках текущего хода (выброса костей)
     *
     * @var bool
     */
    protected $_startMove = false;

    /**
     * Создание нового объекта игральной доски нард
     *
     * @param bool $init
     */
    public function __construct($init = true)
    {
        if ($init) {
            $this->init();
        }
    }

    /**
     * Инициализация игральной доски нард
     *
     * @return void
     */
    public function init()
    {
        //Заполняем все ячейки доски пустыми массивами (+1 для создания ячейки за доской для складывания выведенных шашек)
        $this->_pieces = array_fill(1, self::BOARD_CELL_COUNT + 1, array());
        //Определение начальных ячеек шашек игроков
        $whiteStart = self::BOARD_CELL_COUNT;
        $blackStart = intval(self::BOARD_CELL_COUNT / 2);
        //Установка шашек игроков в начальные позиции
        $this->_pieces[$whiteStart] = array_fill(0, self::PLAYER_PIECE_COUNT, self::WHITE_PIECE);
        $this->_pieces[$blackStart] = array_fill(0, self::PLAYER_PIECE_COUNT, self::BLACK_PIECE);
        //Создание объекта игральных костей
        $this->setDice(new Core_Game_Backgammon_Dice());
    }

    /**
     * Установка шашек
     *
     * @param array $pieces
     */
    public function setPieces(array $pieces)
    {
        $this->_pieces = $pieces;
    }

    /**
     * Получение шашек
     *
     * @return array
     */
    public function getPieces()
    {
        return $this->_pieces;
    }

    /**
     * Установка флага перемещения шашки с начальной позиции в рамках текущего хода (выброса костей)
     *
     * @param bool $startMove
     */
    public function setMoveFromStartCell($startMove = true)
    {
        $this->_startMove = $startMove;
    }

    /**
     * Проверка наличия перемещения шашки с начальной позиции в рамках текущего хода (выброса костей)
     *
     * @return bool
     */
    public function isMoveFromStartCell()
    {
        return $this->_startMove;
    }

    /**
     * Получение массива шашек в ячейке
     *
     * @param int $index Индекс ячейки
     *
     * @return array|null
     */
    public function getCell($index)
    {
        if (isset($this->_pieces[$index])) {
            return $this->_pieces[$index];
        }
        return null;
    }

    /**
     * Установка объекта игральных костей
     *
     * @param Core_Game_Backgammon_Dice $dice
     */
    public function setDice(Core_Game_Backgammon_Dice $dice)
    {
        $this->_dice = $dice;
    }

    /**
     * Получение объекта игральных костей
     *
     * @return Core_Game_Backgammon_Dice
     */
    public function getDice()
    {
        return $this->_dice;
    }

    /**
     * Проверка возможности перемещения шашек у игрока
     *
     * @param Core_Game_Players_Player $player
     *
     * @return bool
     */
    public function canMove(Core_Game_Players_Player $player)
    {
        //Проверка наличия не использованных значений костей
        if (!$this->getDice()->hasFreeValues()) {
            return false;
        }
        //Получаем цвет шашек игрока
        $color = $player->getId();
        //Проверка нахождения всех шашек в "доме"
        if ($this->canThrowOut($player)) {
            //Всгда есть возможность хода
            return true;
        }

        //Объект проверки передвижения шашек
        $movement = new Core_Game_Backgammon_Movement($this);
        //Проходим по всем позициям на игровой доске, где есть шашки игрока
        foreach($this->_pieces as $position => $pieces) {
            //Проверка вхождения позиции в рамки игрового поля
            if ($position < 1 || $position > Core_Game_Backgammon_Board::BOARD_CELL_COUNT) {
                //Ячейка за пределами игрового поля
                continue;
            }
            //Проверка соответствия цвета шашек
            if (!count($pieces) || $this->getCellPiecesColor($position) != $color) {
                //В данной ячейке нет шашек игрока
                continue;
            }
            //Проверка возможности перемещения шашки
            if ($movement->canPieceMove($position, $color)) {
                return true;
            }
        }

        return false;
    }
    /*public function canMove(Core_Game_Players_Player $player)
    {
        //Проверка наличия не использованных значений костей
        if (!$this->getDice()->hasFreeValues()) {
            return false;
        }
        //Получаем цвет шашек игрока
        $color = $player->getId();
        //Проверка нахождения всех шашек в "доме"
        if ($this->canThrowOut($player)) {
            //Всгда есть возможность хода
            return true;
        }
        //Проходим по всем позициям на игровой доске, где есть шашки игрока
        foreach($this->_pieces as $position => $pieces) {
            //Проверка соответствия цвета шашек
            if (!count($pieces) || $this->getCellPiecesColor($position) != $color) {
                //В данной ячейке нет шашек игрока
                continue;
            }
            //Проверка возможности перемещения шашки
            foreach($this->getDice()->getFreePossibleValues() as $value) {
                //Определение конечной позиции перемещения шашки
                $to = $position - $value;
                if ($to <= 0) {
                    $to = $position + (self::BOARD_CELL_COUNT - $value);
                }
                //Проверка валидности перемещения
                if ($this->_isValidMove($position, $to, $color)) {
                    //Возможно перемещение шашки
                    return true;
                }
            }
        }
    } */

    /**
     * Перемещение шашки на игровой доске
     *
     * @param int                      $fromPosition Начальная позиция шашки
     * @param int                      $toPosition   Конечная позиция шашки
     * @param Core_Game_Players_Player $player       Объект игрока
     *
     * @throws Core_Game_Backgammon_Exception
     */
    public function move($fromPosition, $toPosition, Core_Game_Players_Player $player)
    {
        //Получаем цвет шашек игрока
        $color = $player->getId();
        //Проверка вхождения переданных позиций в диапазон возможных значений
        if (!$this->_isValidPosition($fromPosition) || !$this->_isValidPosition($toPosition)) {
            throw new Core_Game_Backgammon_Exception('Invalid move positions');
        }

        //Проверка наличия шашек в указанной позиции
        if (!count($this->_pieces[$fromPosition])) {
            throw new Core_Game_Backgammon_Exception('Position is empty');
        }

        //Проверка наличия в начальной позиции шашки указанного цвета
        if ($this->getCellPiecesColor($fromPosition) != $color) {
            throw new Core_Game_Backgammon_Exception('Piece does not belong to the player');
        }

        //Получаем значение игральной кости(ей) для данного перемещения шашки
        $diceValue = $this->_getMoveValue($fromPosition, $toPosition);
        if ($diceValue <= 0) {
            throw new Core_Game_Backgammon_Exception('Invalid move positions');
        }

        //Получаем список возможных значений для перемещения шашки
        $movement = new Core_Game_Backgammon_Movement($this);
        $movementValues = $movement->getPieceMovementValues($fromPosition, $color);
        //Проверка наличия возможности перемещения на указанное значение игральных костей
        if (!in_array($diceValue, $movementValues)) {
            throw new Core_Game_Backgammon_Exception('Invalid move');
        }

        //Использование игральных костей
        if (!$this->getDice()->useValue($diceValue)) {
            throw new Core_Game_Backgammon_Exception('There is no movement value in dice');
        }

        //Если шашка была выведена из стартовой позиции, фиксируем это действие включением флага
        if ($color == self::WHITE_PIECE && $fromPosition == self::BOARD_CELL_COUNT) {
            $this->setMoveFromStartCell();
        } elseif ($color == self::BLACK_PIECE && $fromPosition == self::BOARD_CELL_COUNT / 2) {
            $this->setMoveFromStartCell();
        }

        //Перемещение шашки
        $piece = array_pop($this->_pieces[$fromPosition]);
        array_push($this->_pieces[$toPosition], $piece);
    }

    /**
     * Проверка возможности вывода шашек
     *
     * @param Core_Game_Players_Player $player
     *
     * @return bool
     */
    public function canThrowOut(Core_Game_Players_Player $player)
    {
        //Получение цвета шашек игрока
        $color = $player->getId();
        //Количество ячеек в одной зоне
        $zoneCellCount = self::BOARD_CELL_COUNT / 4;
        //В зависимости от цвета шашек получаем зону "Дом"
        if ($color == self::WHITE_PIECE) {
            $minCell = 1;
            $maxCell = $zoneCellCount;
        } else {
            $minCell = self::BOARD_CELL_COUNT / 2 + 1;
            $maxCell = $minCell + $zoneCellCount;
        }

        //Флаг наличия шашек игрока на игровом поле
        $hasPieces = false;
        //Поиск шашек игрока вне зоны "Дом"
        foreach($this->_pieces as $position => $pieces) {
            //Проверка наличия шашки игрока в текущей ячейке
            if ($this->getCellPiecesColor($position) !== $color) {
                //Шашки игрока в ячейке нет
                continue;
            }
            //Проверка выхода проверяемой позиции за пределы игрового поля
            if ($position > self::BOARD_CELL_COUNT) {
                //Пропускаем "25" ячейку (выведенные шашки)
                continue;
            }
            //Проверка вхождения позиции в зону "Дом"
            if ($position >= $minCell && $position <= $maxCell) {
                //Установка флага наличия шашки игрока на игровом поле
                $hasPieces = true;
                //Пропускаем ячейку, т.к. мы ищем шашки вне зоны "дома"
                continue;
            }

            //Шашка игрока находится вне зоны "дома", вывод запрещен
            return false;
        }
        //Вне зоны "дома" шашек игрока нет. Возвращаем флаг наличия шашек на игровом поле (в зоне "дома").
        return $hasPieces;
    }

    /**
     * Вывод шашки за пределы игровой доски
     *
     * @param int                      $position Позиция выводимой шашки
     * @param Core_Game_Players_Player $player   Объект игрока
     *
     * @throws Core_Game_Backgammon_Exception
     */
    public function throwOut($position, Core_Game_Players_Player $player)
    {
        //Получение цвета шашек игрока
        $color = $player->getId();
        //Проверка вхождения переданной позиции в диапазон возможных значений
        if (!$this->_isValidPosition($position)) {
            throw new Core_Game_Backgammon_Exception('Invalid piece position');
        }

        //Проверка наличия шашек в указанной позиции
        if (!count($this->_pieces[$position])) {
            throw new Core_Game_Backgammon_Exception('Position is empty');
        }

        //Проверка наличия шашки указанного цвета в переданной позиции
        if ($this->getCellPiecesColor($position) != $color) {
            throw new Core_Game_Backgammon_Exception('Piece does not belong to the player');
        }

        //Получение значения линии за пределами "дома"
        /*if ($color == self::WHITE_PIECE) {
            $throwLine = 0;
        } else {
            $throwLine = self::BOARD_CELL_COUNT / 2;
        }
        //Получение минимального значения игральных костей для вывода шашки
        $minValue = null;
        foreach($this->getDice()->getFreePossibleValues() as $value) {
            if ($position - $value > $throwLine) {
                //Значения не хватает для вывода шашки
                continue;
            }
            if (null === $minValue || $value < $minValue) {
                $minValue = $value;
            }
        }*/

        //Проверка возможности вывода шашки
        $movement = new Core_Game_Backgammon_Movement($this);
        $throwValue = $movement->canThrowout($position, $color);
        if (false === $throwValue) {
            throw new Core_Game_Backgammon_Exception('Invalid throw out action');
        }

        //Вывод шашки
        if ($this->getDice()->useValue($throwValue)) {
            //Достаем верхнюю шашку из ячейки
            $piece = array_pop($this->_pieces[$position]);
            //Добавляем ее в ячейку выведенных шашек, за пределы игровой доски
            array_push($this->_pieces[self::BOARD_CELL_COUNT + 1], $piece);
        }
    }

    /**
     * Получение цвета шашек победителя
     *
     * @return bool|int
     */
    public function getWinnerColor()
    {
        $white = true;
        $black = true;
        foreach($this->_pieces as $position => $pieces) {
            //Проверка только позиций игрового поля
            if ($position > Core_Game_Backgammon_Board::BOARD_CELL_COUNT) {
                continue;
            }
            if (count($pieces)) {
                $color = $this->getCellPiecesColor($position);
                if ($color === Core_Game_Backgammon_Board::WHITE_PIECE) {
                    $white = false;
                } elseif ($color === Core_Game_Backgammon_Board::BLACK_PIECE) {
                    $black = false;
                }
                if (!$white && !$black) {
                    return false;
                }
            }
        }
        //Проверка наличия цвета шашек, которых нет на игровой доске
        if ($white) {
            return Core_Game_Backgammon_Board::WHITE_PIECE;
        } elseif ($black) {
            return Core_Game_Backgammon_Board::BLACK_PIECE;
        } else {
            return false;
        }
    }

    /**
     * Проверка наличия ходов у игрока
     *
     * @param int $color Цвет шашек игрока
     *
     * @return bool
     */
    public function hasMovement($color)
    {
        //Получение начальной позиции шашек игрока
        if ($color == Core_Game_Backgammon_Board::WHITE_PIECE) {
            $startCell = self::BOARD_CELL_COUNT;
        } else {
            $startCell = intval(self::BOARD_CELL_COUNT / 2);
        }
        //Проверка наличия шашек игрока на стартовой позиции
        if ($this->getCellPiecesColor($startCell) != $color) {
            //На стартовой позиции шашки оппонента - хода были
            return true;
        }
        //Проверка наличия всех шашек игрока в начальной позиции
        if (count($this->_pieces[$startCell]) == self::PLAYER_PIECE_COUNT) {
            //Все шашки в начальной позиции - ходов нет
            return false;
        } else {
            //Не все шашки в начальной позиции - хода были
            return true;
        }
    }

    /**
     * Получение данных о шашках на игровой доске в виде строки для каждого цвета.
     *
     * @return array
     */
    public function getPiecesString()
    {
        $colors = array(
            self::WHITE_PIECE => array(),
            self::BLACK_PIECE => array()
        );
        foreach($this->_pieces as $pos => $pieces) {
            //Количество шашек в ячейке
            $count = count($pieces);
            if ($count > 0 && $pos <= self::BOARD_CELL_COUNT) {
                //Данные шашек на игровой доске
                $colors[$this->getCellPiecesColor($pos)][] = $pos . ':' . $count;
            } elseif ($count > 0) {
                //Данные шашек за пределами игровой доски (выведенные шашки).
                //Так как ячейка выведенных шашек одна (25), и белые и черные шашки хранятся в одной ячейке (в одном одномерном массиве)
                //Расчет количества белых и черных шашек в одном одномерном массиве:
                //sum - сумма всех элементов массива;
                //count - количество элементов массива;
                //w - значение белой шашки;
                //b - значение черной шашки.
                //Получение количества белых шашек: countB = |sum - (count * a)|
                //Получение количества черных шашек: countA = |sum - (count * b)|
                $sum = array_sum($pieces);
                $countA = abs($sum - ($count * self::BLACK_PIECE));
                $countB = abs($sum - ($count * self::WHITE_PIECE));
                //Запись данных о выведенных шашках
                if ($countA > 0) {
                    $colors[self::WHITE_PIECE][] = self::BOARD_CELL_COUNT + 1 . ':' . $countA;
                }
                if ($countB > 0) {
                    $colors[self::BLACK_PIECE][] = self::BOARD_CELL_COUNT + 1 . ':' . $countB;
                }
            }
        }
        $colors[self::WHITE_PIECE] = implode(',', $colors[self::WHITE_PIECE]);
        $colors[self::BLACK_PIECE] = implode(',', $colors[self::BLACK_PIECE]);

        return $colors;
    }

    /**
     * Получение цвета шашек в ячейке доски
     *
     * @param int $cellIndex Индекс ячейки на игровой доске
     *
     * @return bool|int
     */
    public function getCellPiecesColor($cellIndex)
    {
        $pieces = $this->getCell($cellIndex);
        $count = count($pieces);
        if (!$count) {
            return false;
        }
        return array_sum($pieces) / $count;
    }

    /**
     * Получение количества ячеек игрового поля, проходимых при перемещении шашки
     *
     * @param int $fromPosition Начальная позиция шашки
     * @param int $toPosition   Конечная позиция шашки
     *
     * @return int
     */
    private function _getMoveValue($fromPosition, $toPosition)
    {
        $diff = $fromPosition - $toPosition;
        if ($diff >= 0) {
            return $diff;
        }

        return $fromPosition + (self::BOARD_CELL_COUNT - $toPosition);
    }

    /**
     * Проверка валидности позиции игровой доски
     *
     * @param int $position Позиция ячейки на игровой доске
     *
     * @return bool
     */
    private function _isValidPosition($position)
    {
        if ($position > 0 && $position <= self::BOARD_CELL_COUNT) {
            return true;
        } else {
            return false;
        }
    }
}
