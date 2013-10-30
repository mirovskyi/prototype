<?php

/**
 * Description of Abstract
 *
 * @author aleksey
 */
abstract class Core_Game_Filler_Abstract extends Core_Game_Abstract
{
    
    /**
     * Идентификатор игрока 1
     */
    const PLAYER_1 = 'A';
    
    /**
     * Иентификатор игрока 2
     */
    const PLAYER_2 = 'B';
    
    /**
     * Время хода
     */
    const STEP_TIMEOUT = 10;
    
    /**
     * Время партии
     */
    const GAME_TIMEOUT = 60;
    
    /**
     * Количество строк в игровой таблице
     * 
     * @var integer 
     */
    protected $_rowCount;
    
    /**
     * Количество столбцов в игровой таблице
     * 
     * @var integer 
     */
    protected $_colCount;
    
    /**
     * Количество цветов в игре
     * 
     * @var integer 
     */
    protected $_colorsCount = 6;
    
    /**
     * Таблица игрового поля
     * 
     * @var array 
     */
    protected $_table = array();
    
    /**
     * Время на ход
     *
     * @var integer
     */
    protected $_runTimeout = self::STEP_TIMEOUT;

    /**
     * Время партии
     *
     * @var int
     */
    protected $_gameTimeout = self::GAME_TIMEOUT;

    /**
     * Количество партий в матче
     *
     * @var int
     */
    protected $_gamesCount = 1;

    /**
     * Количество сыгранных в матче партий
     *
     * @var int
     */
    protected $_gamesPlay = 0;

    /**
     * Идентификатор сессии последнего выигравшего игрока
     *
     * @var string
     */
    protected $_lastWinner;
    
    /**
     * __construct
     * 
     * @param array $table [optional] Данные таблицы игрового поля
     */
    public function __construct($table = null)
    {
        parent::__construct();

        if (is_array($table)) {
            $this->setTable($table);
        }
    }
    
    /**
     * Метод возвращает список полей класса для сериализации.
     * Производится подготовка данных объекта к сериализации
     *
     * @return array
     */
    public function __sleep() 
    {
        foreach($this->getTable() as $row => $rowValue) {
            $this->_table[$row] = implode('', $rowValue);
        }

        //Получаем все своиства объекта
        $properties = array();
        $r = new ReflectionObject($this);
        foreach ($r->getProperties() as $property) {
            $properties[] = $property->getName();
        }

        //Возвращаем массив своиств обэекта для сериализации
        return $properties;
    }
    
    /**
     * Метод выполняется после установки своиств класса из сериализованных данных.
     * Производится востановление данных после unserialize
     */
    public function __wakeup() 
    {
        //Вызов родительского восстановления
        parent::__wakeup();

        $wakeupTable = array();
        foreach($this->getTable() as $row => $rowValue) {
            for($col = 0; $col < strlen($rowValue); $col++) {
                $wakeupTable[$row][$col] = $rowValue[$col];
            }
        }
        $this->setTable($wakeupTable);
    }

    /**
     * Проверка возможности начать игру за игровым столом (изменить статус игры на PLAY)
     *
     * @return bool
     */
    public function canPlay()
    {
        //Начало игры при наличии двух игроков за столом
        if (count($this->getPlayersContainer()) < 2) {
            return false;
        }

        //Проверка статуса игроков
        $play = true;
        foreach($this->getPlayersContainer() as $player) {
            if (!$player->isPlay()) {
                $play = false;
                break;
            }
        }

        return $play;
    }
    
    /**
     * Метод установки количества строк в таблице игрового поля
     * 
     * @param integer $count
     * @return Core_Game_Filler_Abstract 
     */
    public function setRowCount($count)
    {
        $this->_rowCount = $count;
        return $this;
    }
    
    /**
     * Метод получения количества строк в таблице игрового поля
     * 
     * @return integer 
     */
    public function getRowCount()
    {
        return $this->_rowCount;
    }
    
    /**
     * Метод установки количества столбцов в таблице игрового поля
     * 
     * @param integer $count
     * @return Core_Game_Filler_Abstract 
     */
    public function setColCount($count)
    {
        $this->_colCount = $count;
        return $this;
    }
    
    /**
     * Метод получения количества столбцов в таблице игрового поля
     * 
     * @return integer 
     */
    public function getColCount()
    {
        return $this->_colCount;
    }
    
    /**
     * Метод установки таблицы игрового поля
     * 
     * @param array $table
     * @return Core_Game_Filler_Abstract 
     */
    public function setTable(array $table)
    {
        $this->_table = $table;
        return $this;
    }
    
    /**
     * Метод получения таблицы игрового поля
     * 
     * @return array
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * Получение массива строк таблицы
     *
     * @return array
     */
    public function getTableRows()
    {
        //Преобразование игрового поля в массив строк ячеек
        $table = array();
        foreach($this->_table as $row => $rowValue) {
            $table[$row] = implode('', $rowValue);
        }

        return $table;
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
     * @return Core_Game_Filler_Players_Player|Core_Game_Players_Player
     */
    public function addPlayer($sid, $name, $id, $color = null, $runtime = null, $gametime = null, $index = null)
    {
        //Установка времени хода и игры по умолчанию
        if (null === $runtime) {
            $runtime = $this->getRunTimeout();
        }
        if (null === $gametime) {
            $gametime = $this->getGameTimeout();
        }

        //Создание объекта игрока
        $player = new Core_Game_Filler_Players_Player(array(
            'sid' => $sid,
            'name' => $name,
            'id' => $id,
            'color' => $color,
            'runtime' => $runtime,
            'startGametime' => $gametime
        ));
        //Доьавляем игрока
        $this->getPlayersContainer()->addPlayer($player, $index);

        //Возвращаем объект игрока
        return $player;
    }

    /**
     * Добавление оппонента в игру
     *
     * @param string   $sid      Идентификатор сессии оппонента
     * @param string   $name
     * @param int|null $position Позиция места игрового стола, за которое садится игрок
     *
     * @throws Core_Game_Exception
     * @return Core_Game_Filler_Players_Player
     */
    public function addOpponent($sid, $name, $position = null)
    {
        if (null === $position) {
            //Получаем первое свободное место за столом
            foreach($this->getPlaces() as $index => $player) {
                if (null === $player) {
                    $position = $index;
                    break;
                }
            }
        } else {
            //Проверка наличия свободного места по указанной позиции
            if (false !== $this->getPlayersContainer()->getIterator()->getElement($position)) {
                throw new Core_Game_Exception('Game position is allready kept', 212, Core_Exception::USER);
            }
        }

        //Поиск игрока A
        $playerId = Core_Game_Filler_Abstract::PLAYER_1;
        if ($this->getPlayersContainer()->find('id', $playerId)) {
            //Идентификатор оппонента - B
            $playerId = Core_Game_Filler_Abstract::PLAYER_2;
        }

        //Добавление оппонента
        return $this->addPlayer($sid, $name, $playerId, null, null, null, $position);
    }

    /**
     * Установка количества партий в матче
     *
     * @param int $gamesCount
     */
    public function setGamesCount($gamesCount)
    {
        $this->_gamesCount = $gamesCount;
    }

    /**
     * Получение количества партий в матче
     *
     * @return int
     */
    public function getGamesCount()
    {
        return $this->_gamesCount;
    }

    /**
     * Установка количества сыгранных партий в матче
     *
     * @param int $gamesPlay
     */
    public function setGamesPlay($gamesPlay)
    {
        $this->_gamesPlay = $gamesPlay;
    }

    /**
     * Инкремент количества сыгранных партий в матче
     *
     * @return void
     */
    public function incGamesPlay()
    {
        $this->_gamesPlay += 1;
    }

    /**
     * Получение количества сыграных партий в матче
     *
     * @return int
     */
    public function getGamesPlay()
    {
        return $this->_gamesPlay;
    }

    /**
     * Обработка завершения партии
     *
     * @param string $winnerSid Идентификатор сессии победителя
     *
     * @return void
     */
    public function finishGame($winnerSid)
    {
        //Инкремент сыгранных партий в матче
        $this->incGamesPlay();
        //Проверка завершения матча
        if ($this->getGamesPlay() < $this->getGamesCount()) {
            //Установка идентификатора победителя в последней партии
            $this->setLastWinner($winnerSid);
            //Матч не завершен, генерация начального состояния доски для начала новой партии
            $this->generate();
            //Установка статуса окончания партии
            $this->setStatus(Core_Game_Abstract::STATUS_ENDGAME);
            return;
        }

        //Определение победителя в матче по очкам игроков
        $playersPoints = array();
        foreach($this->getPlayersContainer() as $player) {
            $playersPoints[$player->getSid()] = $player->getPoints();
        }
        //Проверка на ничью (одинаковое количество очков)
        if (count(array_unique($playersPoints, SORT_NUMERIC)) <= 1) {
            //TODO: учет комиссии
            //Установка ничьи
            $this->setDraw($this->getBet());
        } else {
            //Сортируем игроков по сумме набранных очков (по убыванию)
            asort($playersPoints, SORT_NUMERIC);
            //Достаем идентификатор сессии игрока с наибольшим количеством очков
            $arrSortPlayers = array_keys($playersPoints);
            $winnerSid = array_pop($arrSortPlayers);
            //Установка победителя
            $this->setWinner($winnerSid);
        }

        //Установка статуса завершения игры
        $this->setStatus(Core_Game_Abstract::STATUS_FINISH);
    }
    
    /**
     * Установка победителя
     *
     * @param Core_Game_Filler_Players_Player|string $player
     * @param int|null $winamount
     */
    public function setWinner($player, $winamount = null)
    {
        //Определение выигрыша
        if (null === $winamount) {
            //TODO: комиссия
            $winamount = $this->getBet() * $this->getMaxPlayersCount();
        }

        //Установка статуса победителя
        parent::setWinner($player, $winamount);
    }

    /**
     * Установка идентификатора сессии победителя в последней партии
     *
     * @param string $sid
     *
     * @return Core_Game_Filler_Abstract
     */
    public function setLastWinner($sid)
    {
        $this->_lastWinner = $sid;
        return $this;
    }

    /**
     * Получение идентификатора сессии победителя в последней партии
     *
     * @return string
     */
    public function getLastWinner()
    {
        return $this->_lastWinner;
    }

    /**
     * Установка текущего статуса игры
     *
     * @param string $status
     * @return Core_Game_Abstract
     */
    public function setStatus($status)
    {
        //Если устанавливается статус окончания игры, сбрасываем количество сыгранных партий в матче (для случая рестарта игры)
        if ($status == Core_Game_Abstract::STATUS_FINISH) {
            $this->setGamesPlay(0);
        }

        return parent::setStatus($status);
    }
    
    /**
     * Проверка наличия победителя 
     *
     * @return Core_Game_Filler_Players_Player|boolean
     */
    public function checkForWinner()
    {
        //Проверка наличия победителя
        foreach($this->getPlayersContainer() as $player) {
            if ($player->getStatus() == Core_Game_Players_Player::STATUS_WINNER) {
                return $player;
            }
        }
        
        //Начальные данные статистики игроков
        $playersStatistic = array(
            self::PLAYER_1 => 0,
            self::PLAYER_2 => 0
        );
        
        //Общее количество ячеек игрового поля
        $allCellsCount = 0;
        
        //Расчет количества заполненных ячеек каждым пользователем
        foreach($this->getTable() as $rowValue) {
            foreach($rowValue as $cell) {
                if (array_key_exists($cell, $playersStatistic)) {
                    $playersStatistic[$cell] = $playersStatistic[$cell] + 1;
                }
                $allCellsCount ++;
            }
        }
        //Проверка заполнения игроком таблицы на 50%
        foreach($playersStatistic as $id => $cellCount) {
            if (($cellCount / $allCellsCount * 100) >= 50) {
                return $this->getPlayersContainer()->find('id', $id);
            }
        }
        
        return false;
    }
    
    /**
     * Метод выбора ячейки игроком
     *
     * @abstract
     * @param int $color
     * @param string $player
     * @return void
     */
    abstract public function selectColor($color, $player);

    /**
     * Добавлние пустого действия в историю анимированного обновления данных игры.
     * В осносном пустые анимации нужны для событий, которые изменяют порядковый номер обновления игры (command)
     * Напимер при увеличении ставки инкрементится прядковый номер, но кроме суммы ставки данные игрового стола не меняются
     * Для того чтобы события не повлияли на порядок отображения анимации, необходимо в историю анимации добавлять пустое действие, которое визуально ничего не меняет за игровым столом
     *
     * @abstract
     * @return void
     */
    public function addEmptyAnimation()
    {
        //Нет анимации
    }

    /**
     * Проверка матчевой игры
     *
     * @return bool
     */
    public function isMatch()
    {
        return $this->_gamesCount > 1;
    }

    /**
     * Проверка наличия действий игрока в текущей партии
     *
     * @param string $playerId
     *
     * @return bool
     */
    public function hasPlayerAction($playerId)
    {
        //Проверка наличия более одной закрытой ячейки игрока
        $closedCell = 0;
        foreach($this->getTable() as $col => $row) {
            foreach($row as $cell) {
                if ($cell == $playerId && ++$closedCell > 1) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Получение текущего состояния игры для записи в историю (XML)
     *
     * @return string
     */
    public function saveHistory()
    {
        //Формирование XML
        $xml = new XMLWriter();
        $xml->openMemory();

        //Данные игроков
        $xml->startElement('pl');
        foreach($this->getPlaces() as $pos => $player) {
            if ($player) {
                $xml->startElement('p');
                $xml->writeAttribute('pos', $pos);
                $xml->writeAttribute('c', $player->getColor());
                $xml->endElement();
            }
        }
        $xml->endElement();

        //Данные игрового стола
        $xml->startElement('pg');
        foreach($this->getTableRows() as $row) {
            $xml->writeElement('s', $row);
        }
        $xml->endElement();

        //Возвращаем данные игры для истории
        return $xml->flush(false);
    }
    
    /**
     * Метод получения случайного кода цвета
     * 
     * @return integer 
     */
    protected function _rand_color()
    {
        return rand(1, $this->_colorsCount);
    }

    /**
     * Проверка валидности выбранного цвета игроком
     *
     * @param Core_Game_Filler_Players_Player $player Объект игрока либо его идентификатор сессии
     * @param integer                         $color  Идентификатор цвета
     * @throws Core_Exception
     */
    protected function _checkSelectedColor($player, $color)
    {
        //Проверка текущего цвета игрока
        if ($player->getColor() == $color) {
            throw new Core_Exception('Received color is already used by the current user', 2001, Core_Exception::USER);
        }
        //Получаем объект оппонента
        $this->getPlayersContainer()->getIterator()->setCurrentElement($player);
        $opponent = $this->getPlayersContainer()->getIterator()->nextElement();
        //Проверка текущего цвета оппонента
        if ($opponent->getColor() == $color) {
            throw new Core_Exception('Received color is already used by opponent', 2002, Core_Exception::USER);
        }
    }
}
