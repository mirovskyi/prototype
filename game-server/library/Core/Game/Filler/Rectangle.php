<?php

/**
 * Description of Rectangle
 *
 * @author aleksey
 */
class Core_Game_Filler_Rectangle extends Core_Game_Filler_Abstract 
{
    
    /**
     * Системное имя игры
     */
    const GAME_NAME = 'filler';
    
    /**
     * Количество строк в игровом поле
     * @var integer 
     */
    protected $_rowCount = 20;
    
    /**
     * Количество столбцов в игровом поле
     * @var integer 
     */
    protected $_colCount = 24;
    
    
    /**
     * Получение системного имени игры
     *
     * @return string
     */
    public function getName()
    {
        return self::GAME_NAME;
    }
    
    /**
     * Метод создания игрового поля
     */
    public function generate() 
    {
        //Заполнение таблицы игрового поля идентификторавми цветов
        for($row = 0; $row < $this->getRowCount(); $row++) {
            for($col = 0; $col < $this->getColCount(); $col++) {
                $this->_table[$row][$col] = $this->_rand_color();
            }
        }

        //Установка начальных позиций игроков
        $this->_table[0][0] = self::PLAYER_1;
        $this->_table[$this->getRowCount() - 1][$this->getColCount() - 1] = self::PLAYER_2;

        //Проверка наличия пользователей за игровым столом
        if (count($this->getPlayersContainer())) {
            //Установка начальных цветов пользователей, обновление таймеров и статусов
            foreach($this->getPlayersContainer() as $player) {
                $player->setColor($this->_calculatePlayerColor($player->getId()));
                $player->setStartGametime($this->getGameTimeout());
                //Если в предыдущей партии игрок победил - его первый ход
                if ($player == $this->getLastWinner()) {
                    $this->getPlayersContainer()->setActive($player);
                }
                //Обнуление статуса игрока
                $player->setStatus(Core_Game_Players_Player::STATUS_NONE);
            }
        }
    }

    /**
     * Добавление игрока
     *
     * @param string   $sid      Идентификатор сессии пользователя
     * @param string   $name     Имя игрока
     * @param string   $id       Идентификатор пользователя в игре (A|B)
     * @param int      $color    Идентификатор текущего цвета полей пользователя
     * @param int|null $runtime  Время игрока на ход
     * @param int|null $gametime Время игрока на партию
     * @param int|null $index    Порядковый номер пользователя в игре
     *
     * @return Core_Game_Filler_Players_Player
     */
    public function addPlayer($sid, $name, $id, $color = null, $runtime = null, $gametime = null, $index = null)
    {
        //Проверка цвета
        if (null === $color) {
            //Определение начального цвета игрока
            $color = $this->_calculatePlayerColor($id);
        }

        //Добавляем игрока
        return parent::addPlayer($sid, $name, $id, $color, $runtime, $gametime, $index);
    }


    /**
     * Метод выбора ячейки пользователем
     *
     * @param integer                                $color  Идентификатор цвета
     * @param Core_Game_Filler_Players_Player|string $player Объект игрока либо его идентификатор сессии
     * @throws Core_Game_Exception
     */
    public function selectColor($color, $player) 
    {
        //Получение объекта игрока
        if (!$player instanceof Core_Game_Players_Player) {
            if (!is_scalar($player)) {
                throw new Core_Game_Exception('Invalid filler player type');
            }

            $player = $this->getPlayersContainer()->getPlayer($player);
        }

        //Проверка валидности цвета
        $this->_checkSelectedColor($player, $color);
        //Поиск соседних ячеек пользователя с указанным цветом
        foreach($this->getTable() as $row => $rowValue) {
            foreach($rowValue as $col => $cell) {
                if ($this->_table[$row][$col] == $player->getId()) {
                    $this->_findNeighboring($row, $col, $color, $player->getId());
                }
            }
        }
        //Поиск замкнутых ячеек
        $this->_findClosedCells($player->getId());
    }

    /**
     * Определение начального цвета ячейки игрока
     *
     * @param int $playerId Идентификатор игрока в игре
     * @return int
     */
    protected function _calculatePlayerColor($playerId)
    {
        //Список всех возможных цветов (массив из идентификаторов цветов, от 1 до $this->_colorsCount)
        $listColors = range(1, $this->_colorsCount);

        //Получение сгенерированных цветов соседних ячеек, и идентификатора оппонента
        if ($playerId == Core_Game_Filler_Abstract::PLAYER_1) {
            //Цвета соседних ячеек
            $denyColors = array($this->_table[1][0], $this->_table[0][1]);
            //Идентификатор оппонента
            $opponentId = Core_Game_Filler_Abstract::PLAYER_2;
        } else {
            //Цвета соседних ячеек
            $denyColors = array(
                $this->_table[$this->getRowCount() - 2][$this->getColCount() - 1],
                $this->_table[$this->getRowCount() - 1][$this->getColCount() - 2]
            );
            //Идентификатор оппонента
            $opponentId = Core_Game_Filler_Abstract::PLAYER_1;
        }

        //Проверка наличия оппонента
        $opponentPlayer = $this->getPlayersContainer()->find('id', $opponentId);
        if (false !== $opponentPlayer) {
            //Добавляем в список неразрешенных цветов цвет оппонента
            array_push($denyColors, $opponentPlayer->getColor());
        }

        //Получаем список доступных цветов
        $allowColors = array_diff($listColors, $denyColors);
        //Выбираем из списка доступных цветов случайный элемент
        $color = $allowColors[array_rand($allowColors)];

        //Возвращаем начальный цвет пользователя
        return $color;
    }
    
    /**
     * Метод поиска цвета в соседних ячейках пользователя и присвоение ячеек пользователю
     * @param integer $row Номер строки ячейки
     * @param integer $col Номер столбца ячейки
     * @param integer $color Идентификатор цвета
     * @param string $playerId Идентификатор игрока
     */
    public function _findNeighboring($row, $col, $color, $playerId)
    {
        //Присвоение ячейки пользователю
        $this->_table[$row][$col] = $playerId;
        //Проверка соседних ячеек
        if ($this->_isCellInColor($row - 1, $col, $color)) {
            $this->_findNeighboring($row - 1, $col, $color, $playerId);
        }
        if ($this->_isCellInColor($row + 1, $col, $color)) {
            $this->_findNeighboring($row + 1, $col, $color, $playerId);
        }
        if ($this->_isCellInColor($row, $col - 1, $color)) {
            $this->_findNeighboring($row, $col - 1, $color, $playerId);
        }
        if ($this->_isCellInColor($row, $col + 1, $color)) {
            $this->_findNeighboring($row, $col + 1, $color, $playerId);
        }
    }
    
    /**
     * Метод проврки цвета ячейки
     * @param integer $x
     * @param integer $y
     * @param string $color
     * @return boolean 
     */
    protected function _isCellInColor($x, $y, $color)
    {
        if (isset($this->_table[$x][$y]) &&
                $this->_table[$x][$y] == $color) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Поиск замкнутых ячеек
     * @param string $playerId Идентификатор игрока (A/B)
     */
    protected function _findClosedCells($playerId)
    {
        //Копия таблицы игрового поля
        $table = $this->getTable();
        //Определение координат угла противника
        if ($playerId == self::PLAYER_1) {
            $row = $this->getRowCount() - 1;
            $col = $this->getColCount() - 1;
        } else {
            $row = 0;
            $col = 0;
        }
        //Заполняем всю чужую и общую область нулями
        $this->_findNonPlayerNeighboring($table, $playerId, $row, $col);
        //Все не нулевые ячейки присваиваем пользователю
        foreach($table as $row => $rowValue) {
            foreach($rowValue as $col => $cell) {
                if ($cell != 0) {
                    //Присваиваем ячейку пользователю
                    $this->_table[$row][$col] = $playerId;
                }
            }
        }
    }
    
    /**
     * Метод заполнения соседних ячеек нулями, если это ячеки не принадлежат пользователю
     * @param array $table
     * @param string $playerId
     * @param integer $row
     * @param integer $col 
     */
    protected function _findNonPlayerNeighboring(&$table, $playerId, $row, $col)
    {
        $table[$row][$col] = 0;
        if (isset($table[$row + 1]) && $table[$row + 1][$col] != $playerId) {
            $this->_findNonPlayerNeighboring($table, $playerId, $row + 1, $col);
        }
        if (isset($table[$row - 1]) && $table[$row - 1][$col] != $playerId) {
            $this->_findNonPlayerNeighboring($table, $playerId, $row - 1, $col);
        }
        if (isset($table[$row][$col + 1]) && $table[$row][$col + 1] != $playerId) {
            $this->_findNonPlayerNeighboring($table, $playerId, $row, $col + 1);
        }
        if (isset($table[$row][$col - 1]) && $table[$row][$col - 1] != $playerId) {
            $this->_findNonPlayerNeighboring($table, $playerId, $row, $col - 1);
        }
    }
    
}