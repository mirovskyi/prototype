<?php

/**
 * Description of Chess
 *
 * @author aleksey
 */
class Core_Game_Chess extends Core_Game_Abstract 
{

    /**
     * Системное имя игры
     */
    const GAME_NAME = 'chess';

    /**
     * Время хода
     */
    const STEP_TIMEOUT = 60;

    /**
     * Время партии
     */
    const GAME_TIMEOUT = 180;
    
    /**
     * Начальная позиция фигур на шахматной доске
     */
    const START_WHITE_POSITION = 'RA1,HB1,BC1,QD1,KE1,BF1,HG1,RH1,PA2,PB2,PC2,PD2,PE2,PF2,PG2,PH2';
    const START_BLACK_POSITION = 'RA8,HB8,BC8,QD8,KE8,BF8,HG8,RH8,PA7,PB7,PC7,PD7,PE7,PF7,PG7,PH7';

    /**
     * Время хода
     *
     * @var int
     */
    protected $_runTimeout = self::STEP_TIMEOUT;

    /**
     * Время партии
     *
     * @var int
     */
    protected $_gameTimeout = self::GAME_TIMEOUT;
    
    /**
     * Объект шахматной доски
     *
     * @var Core_Game_Chess_Board 
     */
    protected $_board;

    /**
     * Сумма ставки
     *
     * @var int
     */
    protected $_bet = 10;

    /**
     * Количество партий
     *
     * @var int
     */
    protected $_gamesCount = 1;

    /**
     * Количество сыгранных партий
     *
     * @var int
     */
    protected $_gamesPlay = 0;
    
    
    /**
     * __construct
     */
    public function __construct() 
    {
        parent::__construct();

        //Создание объекта шахматной доски
        $this->setChessBoard(new Core_Game_Chess_Board());
    }
    
    /**
     * Клонирование объекта
     */
    public function __clone()
    {
        $this->setChessBoard(clone ($this->_board));
    }
    
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
     * Установка фигур шахматной доски в начальную позицию
     */
    public function generate()
    {
        //Создание нового объекта шахматной доски
        $this->setChessBoard(new Core_Game_Chess_Board());

        //Расставление белых фигур на доску
        $this->getChessBoard()->fromString(self::START_WHITE_POSITION,
                                             Core_Game_Chess_Piece_Abstract::WHITE);
        //Расставление черных фигур на доску
        $this->getChessBoard()->fromString(self::START_BLACK_POSITION,
                                             Core_Game_Chess_Piece_Abstract::BLACK);

        //Обновление таймеров пользователей, обнуление статусов игроков
        foreach($this->getPlayersContainer() as $player) {
            $player->setStartGametime($this->getGameTimeout());
            $player->setStatus(Core_Game_Players_Player::STATUS_NONE);
            //Если цвет фигур белый - установка игрока как активного (первый ход)
            if ($player->getId() == Core_Game_Chess_Piece_Abstract::WHITE) {
                $this->getPlayersContainer()->setActive($player);
            }
        }
    }

    /**
     * Добавление оппонента в игру
     *
     * @param string   $session
     * @param string   $name
     * @param int|null $position Позиция места игрового стола, за которое садится игрок
     *
     * @throws Core_Game_Exception
     * @return Core_Game_Players_Player
     */
    public function addOpponent($session, $name, $position = null)
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

        //Определение цвета фигур
        $iterator = $this->getPlayersContainer()->getIterator();
        if ($iterator->getCurrentElement()->getId() == Core_Game_Chess_Piece_Abstract::WHITE) {
            $color = Core_Game_Chess_Piece_Abstract::BLACK;
        } else {
            $color = Core_Game_Chess_Piece_Abstract::WHITE;
        }

        //Добаляем оппонента
        $player = $this->addPlayer($session, $name, $color, null, null, $position);
        //Если цвет фигур белый, устанавливаем пользователя как активного (белые всегда ходят первыми)
        if ($color == Core_Game_Chess_Piece_Abstract::WHITE) {
            $this->getPlayersContainer()->setActive($player);
        }

        //Возвращаем добавленного игрока
        return $player;
    }

    /**
     * Получение цвета фигур оппонента
     *
     * @param int $color Цвет фигур текущего игрока
     * @return int
     */
    public function getOpponentColor($color)
    {
        if ($color == Core_Game_Chess_Piece_Abstract::WHITE) {
            return Core_Game_Chess_Piece_Abstract::BLACK;
        } else {
            return Core_Game_Chess_Piece_Abstract::WHITE;
        }
    }
    
    /**
     * Установка объекта шахматной доски
     *
     * @param Core_Game_Chess_Board $board
     * @return Core_Game_Chess 
     */
    public function setChessBoard(Core_Game_Chess_Board $board)
    {
        $this->_board = $board;
        return $this;
    }
    
    /**
     * Получение объекта шахматной доски
     *
     * @return Core_Game_Chess_Board 
     */
    public function getChessBoard()
    {
        return $this->_board;
    }

    /**
     * Установка количество партий в матче
     *
     * @param int $count
     *
     * @return Core_Game_Chess
     */
    public function setGamesCount($count)
    {
        $this->_gamesCount = $count;
        return $this;
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
     * Проверка матчевой игры
     *
     * @return bool
     */
    public function isMatch()
    {
        return $this->_gamesCount > 1;
    }

    /**
     * Установка количества сыгранных партий
     *
     * @param int $count
     *
     * @return Core_Game_Chess
     */
    public function setGamesPlay($count)
    {
        $this->_gamesPlay = $count;
        return $this;
    }

    /**
     * Инкремент количества сыгранных матчей
     *
     * @return void
     */
    public function incGamesPlay()
    {
        $this->_gamesPlay += 1;
    }

    /**
     * Получение количества сыгранных партий
     *
     * @return int
     */
    public function getGamesPlay()
    {
        return $this->_gamesPlay;
    }

    /**
     * Получение цвета фигур игрока к которому применяется событие
     *
     * @return int|bool
     */
    public function getEventColor()
    {
        switch ($this->getChessBoard()->getEvent()) {
            case Core_Game_Chess_Board::CHECK:
            case Core_Game_Chess_Board::CHECKMATE:
            case Core_Game_Chess_Board::PROMOTION:
                return $this->getPlayersContainer()->getActivePlayer()->getId();
            default: return false;
        }
    }

    /**
     * Перемещение фигуры на щахматной доске
     *
     * @throws Core_Game_Chess_Exception
     * @param Core_Game_Chess_Coords_Position|string $fromPosition
     * @param Core_Game_Chess_Coords_Position|string $toPosition
     * @return Core_Game_Chess_Piece_Abstract
     */
    public function move($fromPosition, $toPosition)
    {
        //Получаем объект фигуры по ее позиции на шахматной доске
        $piece = $this->getChessBoard()->getPiece($fromPosition);
        if (!$piece) {
            throw new Core_Game_Chess_Exception('No piece for the given position', 2051, Core_Exception::USER);
        }
        
        //Запоминаем текущее состояние доски
        $state = $this->getChessBoard()->__toString();

        //Попытка перемещения фигуры
        if (is_string($toPosition)) {
            $toPosition = new Core_Game_Chess_Coords_Position($toPosition);
        }
        if (!$piece->move($toPosition, $this->getCommand())) {
            throw new Core_Game_Chess_Exception('Invalid move', 2052, Core_Exception::USER);
        }
        
        //Записываем состояние доски до перемещения фигуры
        $this->getChessBoard()->setPreviousBoardState($state);

        //Добавляем состояние шахматной доски в историю
        $this->getChessBoard()->addHistoryItem();
        //Возвращаем объект фигуры, данные которой были изменены
        return $piece;
    }

    /**
     * Завершение партии
     *
     * @return void
     */
    public function finishGame()
    {
        //Инкремент сыгранных партий
        $this->incGamesPlay();
        //Проверка окончания матча
        if ($this->getGamesPlay() < $this->getGamesCount()) {
            //Матч не окончен
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
     * @param Core_Game_Players_Player|string $player
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
     * Добавлние пустого действия в историю анимированного обновления данных игры.
     * В осносном пустые анимации нужны для событий, которые изменяют порядковый номер обновления игры (command)
     * Напимер при увеличении ставки инкрементится прядковый номер, но кроме суммы ставки данные игрового стола не меняются
     * Для того чтобы события не повлияли на порядок отображения анимации, необходимо в историю анимации добавлять пустое действие, которое визуально ничего не меняет за игровым столом
     *
     * @return void
     */
    public function addEmptyAnimation()
    {
        $this->getChessBoard()->getAnimation()->addAction($this->getCommand(), Core_Game_Chess_Animation::EMPTY_ANIMATION);
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

        //Событие на игровой доске
        if ($this->getChessBoard()->isEvent() && $this->getChessBoard()->getEvent() != Core_Game_Chess_Board::PROMOTION) {
            $xml->writeElement('event', $this->getChessBoard()->getEvent());
        }

        //Данные игровой доски
        $xml->writeElement('pieces', $this->getChessBoard()->__toString());

        //Возвращаем данные игры для истории
        return $xml->flush(false);
    }

    /**
     * Получение данных игры в виде XML
     *
     * @return string
     */
    public function saveXml()
    {
        //Формирование XML
        $xml = new XMLWriter();
        $xml->openMemory();

        //Данные игровой доски
        $xml->writeElement('pieces', $this->getChessBoard()->__toString());

        //Возвращаем данные игры для истории
        return $xml->flush(false);
    }
}