<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.07.12
 * Time: 10:51
 *
 *
 */
class Core_Game_Backgammon_Movement
{

    /**
     * Ссылка на данные игровой доски
     *
     * @var Core_Game_Backgammon_Board
     */
    protected $_board;


    /**
     * Construct
     *
     * @param Core_Game_Backgammon_Board $board
     */
    public function __construct(Core_Game_Backgammon_Board $board)
    {
        $this->_board = $board;
    }

    /**
     * Получение ссылки на данные игровой доски
     *
     * @return Core_Game_Backgammon_Board
     */
    public function getBoard()
    {
        return $this->_board;
    }

    /**
     * Проверка возможности хода шашки
     *
     * @param int $cell  Позиция ячейки шашки
     * @param int $color Цвет шашки
     *
     * @return bool
     */
    public function canPieceMove($cell, $color)
    {
        //Получение списка уникальных значение игральных костей, которые еще не были использованы
        $values = array_unique($this->getBoard()->getDice()->getFreeValues());
        //Проверка возможности хода по полученным значениям
        foreach($values as $value) {
            if ($this->_canPieceMoveValue($cell, $color, $value)) {
                //Перемещение шашек из данной ячейки возможно
                return true;
            }
        }

        return false;
    }

    /**
     * Проверка возможности вывода шашки из указанной ячейки дома
     *
     * @param int $cell  Идентификатор ячейки на игровой доске
     * @param int $color Цвет шашки
     *
     * @return int|bool Возвращает значение используемой игральной кости если есть возможность вывода, иначе FALSE
     */
    public function canThrowout($cell, $color)
    {
        //Определение ячейки за пределами "дома", за которую необходимо вывести шашки
        if ($color == Core_Game_Backgammon_Board::WHITE_PIECE) {
            $throwCell = 0;
        } else {
            $throwCell = Core_Game_Backgammon_Board::BOARD_CELL_COUNT / 2;
        }

        //Проверка наличия значений на игральных костях для перехода в ячейку вывода
        $freeValues = array_unique($this->getBoard()->getDice()->getFreeValues());
        foreach($freeValues as $value) {
            $endPosition = $cell - $value;
            if ($endPosition == $throwCell) {
                return $value;
            }
        }

        //Проверяем есть ли значения на игральных костях для вывода шашек из большего разряда
        $highValue = null;
        foreach($freeValues as $value) {
            if ($cell - $value < $throwCell) {
                $highValue = $value;
                break;
            }
        }

        //Если нет значения костей для перемещения шашек из высшего разряда, вывод не возможен
        if (null === $highValue) {
            return false;
        }
        //Если есть значения костей для перемещения шашек из высшего разряда, проверяем наличия шашек в ячейках высшего разряда
        for ($i = 1; $i <= 6; $i++) {
            if ($this->getBoard()->getCellPiecesColor($cell + $i) === $color) {
                //Есть шашки в ячейки большего разряда
                return false;
            }
        }

        //Вывод разрешен по значению большего разряда
        return $highValue;
    }

    /**
     * Получение списока возможных значений игральных костей для хода из ячейки игральной доски
     *
     * @param int $cell  Позиция ячейки на игральной доске
     * @param int $color Цвет шашек в ячейке
     *
     * @return array
     */
    public function getPieceMovementValues($cell, $color)
    {
        //Список возможных ячеек для перехода
        $movementValues = array();

        //Получение списка уникальных значений игральных костей, которые еще не были использованы
        $values = array_unique($this->getBoard()->getDice()->getFreeValues());
        //Проверка возможности хода по полученным значениям
        foreach($values as $value) {
            if ($this->_canPieceMoveValue($cell, $color, $value)) {
                //Добавляем в массив возможных значений для хода
                $movementValues[] = $value;
            }
        }
        //Если нет возможности походить на значения выброщенных костей, хода из указанной ячейки нет
        if (!count($movementValues)) {
            return $movementValues;
        }

        //Проверка воможности ходов на суммарные значения выброшенных костей
        foreach ($this->getBoard()->getDice()->getFreeSumValues() as $value) {
            if ($this->_canPieceMoveValue($cell, $color, $value)) {
                $movementValues[] = $value;
            } else {
                //Если одно из суммарных значений не проходит, дальше хода нет
                //Например выпало дубль 2: первые 2 проходит, вторые 2 т.е. 4 проходит, а 6 не проходит - значит на 8 тоже хода нет, т.к. через 6 шашка должна пройти
                break;
            }
        }

        //Возвращаем список возможных значений игральных костей для хода из ячейки
        return $movementValues;
    }

    /**
     * Получение возможных позиций для перемещения шашки
     *
     * @param int $cell  Позиция ячейки шашки на игровой доске
     * @param int $color Цвет шашки
     *
     * @return array
     */
    public function getPieceMovementPositions($cell, $color)
    {
        //Список возможных поизиций шашки для перемещения
        $cells = array();
        //Получение списка возможных значений игральных костей для перемещения шашки
        $movementValues = $this->getPieceMovementValues($cell, $color);
        if (count($movementValues)) {
            //Получение позиции перемещения для каждого значения игральной кости
            foreach($movementValues as $value) {
                $to = $cell - $value;
                if ($to <= 0) {
                    $to = $cell + (Core_Game_Backgammon_Board::BOARD_CELL_COUNT - $value);
                }
                $cells[] = $to;
            }
        }

        return $cells;
    }

    /**
     * Проверка возможности перемещения шашки из указанной ячейки на указанное значение игральной кости
     *
     * @param int $cell  Позиция текущей ячейки шашки
     * @param int $color Цвет шашки
     * @param int $value Значение на игральной кости
     *
     * @return bool
     */
    private function _canPieceMoveValue($cell, $color, $value)
    {
        //Проверка переданного значения
        if ($value <= 0) {
            return false;
        }
        //Получаем количество ячеек на игровой доске
        $cellCount = Core_Game_Backgammon_Board::BOARD_CELL_COUNT;
        //Получаем позицию ячейки на которую необходимо переместить шашку
        $to = $cell - $value;
        if ($to <= 0) {
            $to = $cell + ($cellCount - $value);
        }
        //Проверка выхода найденной ячейки за пределы игрового поля
        if ($to <= 0 || $to > $cellCount) {
            return false;
        }

        //Проверка перехода шашек за пределы "дома"
        if ($color == Core_Game_Backgammon_Board::BLACK_PIECE) { //Для черных шашек
            $firstHouseLine = Core_Game_Backgammon_Board::BOARD_CELL_COUNT / 2 + 1;
            if (($cell - $value <= 0 || $cell >= $firstHouseLine) && $to < $firstHouseLine) {
                return false;
            }
        } elseif ($cell < $to) { //Для белых шашек (идет от 24 ячейки до 1)
            return false;
        }


        //Проверка воможности перемещения
        $toColor = $this->getBoard()->getCellPiecesColor($to);
        if ($toColor !== false && $toColor != $color) {
            return false;
        }

        //Проверка вывода шашки из начальной позиции
        if ($color == Core_Game_Backgammon_Board::WHITE_PIECE) {
            $startPosition = Core_Game_Backgammon_Board::BOARD_CELL_COUNT;
        } else {
            $startPosition = intval(Core_Game_Backgammon_Board::BOARD_CELL_COUNT / 2);
        }
        if ($cell == $startPosition) {
            if (!$this->_canMoveFromStartPosition($cell, $color)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверка возможности вывода шашки из стартовой ячейки
     *
     * @param int $cell  Позиция стартовой ячейки
     * @param int $color Цвет шашки
     *
     * @return bool
     */
    private function _canMoveFromStartPosition($cell, $color)
    {
        //Если за текущий ход не было вывода шашки из начальной позиции - вывод возможен
        if (!$this->getBoard()->isMoveFromStartCell()) {
            return true;
        }
        //Если выведено больше одной шашки вывод запрещен
        if (count($this->getBoard()->getCell($cell)) < Core_Game_Backgammon_Board::PLAYER_PIECE_COUNT - 1) {
            return false;
        }

        //Текущий ход первый и была выведена только одна шашка, ищем эту шашку на игровой доске
        $pieceCell = null;
        foreach($this->getBoard()->getPieces() as $position => $pieces) {
            if (count($pieces) && $this->getBoard()->getCellPiecesColor($position) == $color) {
                $pieceCell = $position;
                break;
            }
        }
        //Проверка возможности хода шашки
        $freeValues = array_unique($this->getBoard()->getDice()->getFreeValues());
        foreach ($freeValues as $value) {
            if ($this->_canPieceMoveValue($pieceCell, $color, $value)) {
                //У шашки есть возможность хода, вывод шашек из стартовой позиции запрещен
                return false;
            }
        }

        return true;
    }

}
