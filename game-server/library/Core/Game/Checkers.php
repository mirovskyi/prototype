<?php

/**
 * Description of Chess
 *
 * @author aleksey
 */
class Core_Game_Checkers extends Core_Game_Abstract 
{

    /**
     * Системное имя игры
     */
    const GAME_NAME = 'checkers';

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
    const START_WHITE_POSITION = 'A1,C1,E1,G1,B2,D2,F2,H2,A3,C3,E3,G3';
    const START_BLACK_POSITION = 'B6,D6,F6,H6,A7,C7,E7,G7,B8,D8,F8,H8';

    /**
     * Время на ход
     *
     * @var int
     */
    protected $_runTimeout = self::STEP_TIMEOUT;

    /**
     * Время на партию
     *
     * @var int
     */
    protected $_gameTimeout = self::GAME_TIMEOUT;
    
    /**
     * Объект игровой доски
     *
     * @var Core_Game_Checkers_Board 
     */
    protected $_board;

    /**
     * Объект истории анимаций в игре
     *
     * @var Core_Game_Checkers_Animation
     */
    protected $_animation;

    /**
     * Количество партий в матче
     *
     * @var int
     */
    protected $_gamesCount = 1;

    /**
     * Количество сыгранных партий матче
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
        $this->setBoard(new Core_Game_Checkers_Board());
        //Создание объекта истории анимаций
        $this->setAnimation(new Core_Game_Checkers_Animation());

    }
    
    /**
     * Клонирование объекта
     */
    public function __clone()
    {
        $this->setBoard(clone ($this->_board));
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
     * Установка шашек на шахматной доске в начальные позиции
     */
    public function generate()
    {
        //Создание нового объекта доски
        $this->setBoard(new Core_Game_Checkers_Board());

        //Расставление белых шашек на доску
        $this->getBoard()->fromString(self::START_WHITE_POSITION,
                                        Core_Game_Checkers_Piece::WHITE);
        //Расставление черных шашек на доску
        $this->getBoard()->fromString(self::START_BLACK_POSITION,
                                        Core_Game_Checkers_Piece::BLACK);

        //Обновление таймеров пользователей, обнуление статусов пользователей
        foreach($this->getPlayersContainer() as $player) {
            $player->setStartGametime($this->getGameTimeout());
            $player->setStatus(Core_Game_Players_Player::STATUS_NONE);
            //Белые всегда ходят первыми
            if ($player->getId() == Core_Game_Checkers_Piece::WHITE) {
                $this->getPlayersContainer()->setActive($player);
            }
        }

        //Очистка истории анимаций
        $this->getAnimation()->clear();
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

        //Определение цвета шашек
        $iterator = $this->getPlayersContainer()->getIterator();
        if ($iterator->getCurrentElement()->getId() == Core_Game_Checkers_Piece::WHITE) {
            $color = Core_Game_Checkers_Piece::BLACK;
        } else {
            $color = Core_Game_Checkers_Piece::WHITE;
        }

        //Добаляем оппонента
        $player = $this->addPlayer($session, $name, $color, null, null, $position);
        //Если цвет шашек белые, устанавливаем пользователя как активного (белые всегда ходят первыми)
        if ($color == Core_Game_Checkers_Piece::WHITE) {
            $this->getPlayersContainer()->setActive($player);
        }

        //Возвращаем добавленного игрока
        return $player;
    }

    /**
     * Получение цвета шашек оппонента
     *
     * @param int $color Цвет шашки текущего игрока
     * @return int
     */
    public function getOpponentColor($color)
    {
        if ($color == Core_Game_Checkers_Piece::WHITE) {
            return Core_Game_Checkers_Piece::BLACK;
        } else {
            return Core_Game_Checkers_Piece::WHITE;
        }
    }
    
    /**
     * Установка объекта шахматной доски
     *
     * @param Core_Game_Checkers_Board $board
     * @return Core_Game_Checkers 
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
     * Установка объекта истории анимаций
     *
     * @param Core_Game_Checkers_Animation $animation
     *
     * @return Core_Game_Checkers
     */
    public function setAnimation(Core_Game_Checkers_Animation $animation)
    {
        $this->_animation = $animation;
        return $this;
    }

    /**
     * Получение объекта истории анимации
     *
     * @return Core_Game_Checkers_Animation
     */
    public function getAnimation()
    {
        return $this->_animation;
    }

    /**
     * Установка количества партий в матче
     *
     * @param int $count
     *
     * @return Core_Game_Checkers
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
     * Установка количества сыгранных партий в матче
     *
     * @param int $count
     *
     * @return Core_Game_Checkers
     */
    public function setGamesPlay($count)
    {
        $this->_gamesPlay = $count;
        return $this;
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
     * Получение количества сыгранных партий в матче
     *
     * @return int
     */
    public function getGamesPlay()
    {
        return $this->_gamesPlay;
    }

    /**
     * Перемещение шашки на шахматной доске
     *
     * @throws Core_Game_Checkers_Exception
     * @param Core_Game_Chess_Coords_Position|string $fromPosition
     * @param Core_Game_Chess_Coords_Position|string $toPosition Позиция перемещения. Может быть цепочка позиций в формате E5:C3:A1
     * @return Core_Game_Checkers_Piece
     */
    public function move($fromPosition, $toPosition)
    {
        //Получаем объект шашки по ее позиции на шахматной доске
        $piece = $this->getBoard()->getPiece($fromPosition);
        if (!$piece) {
            throw new Core_Game_Checkers_Exception('No piece for the given position', 2051, Core_Exception::USER);
        }
        
        //Позиция перемещения должна быть строкой
        if ($toPosition instanceof Core_Game_Chess_Coords_Position) {
            $toPosition = $toPosition->getPosition();
        }
        
        //Проверка валидности типа данных позиции перемещения
        if (!is_string($toPosition)) {
            throw new Core_Game_Checkers_Exception('Invalid data type of move position');
        }
        
        //Разбиваем позиции перемещения на части
        $arrPositions = explode(':', $toPosition);
        //По очереди перемещаем шашку на все позиции
        foreach($arrPositions as $position) {
            //Запоминаем текущее состояние шашки для записи в историю анимации
            $oldPosition = $piece->getPosition()->getPosition();
            $isKing = $piece->isKing();
            //Объект позиции
            $position = new Core_Game_Chess_Coords_Position($position);
            //Перемещение шашки
            if (!$piece->move($position, count($arrPositions) > 1)) {
                throw new Core_Game_Checkers_Exception('Invalid move', 2052, Core_Exception::USER);
            }
            //Сохраняем перемещение в историю анимаций
            $this->getAnimation()->addAction(
                $this->getCommand(),
                Core_Game_Checkers_Animation::MOVE,
                $oldPosition,
                $position->getPosition(),
                $isKing != $piece->isKing()
            );
        }
        
        //Проверка наличия сбитых шашек противника
        $killed = $this->getBoard()->getLastKilledPieces();
        if (count($killed)) {
            //Провяем возможность сбить еще шашки противника
            if ($piece->hasKill()) {
                //Есть еще шашки противника под ударом, цепочка ходов не завершена
                throw new Core_Game_Checkers_Exception('Invalid move', 2052, Core_Exception::USER);
            }
            //Проверка наличия шашки противника для сбития по направлению хода дамки от последней сбитой шашки
            if ($piece->hasPieceForKillInLastMoveWay()) {
                //Не верный ход дамкой, есть еще шашки соперника которые можно было сбить
                throw new Core_Game_Checkers_Exception('Invalid move', 2052, Core_Exception::USER);
            } 
            //Удаляем все сбитые шашки с игрового поля
            foreach($killed as $killedPiece) {
                //Удаление шашки
                $piece->getBoard()->unsetPiece($killedPiece->getPosition());
                //Запись действие в историю анимаций
                $this->getAnimation()->addAction(
                    $this->getCommand(),
                    Core_Game_Checkers_Animation::BEAT_OFF,
                    $killedPiece->getPosition()->getPosition()
                );
            }
        }

        //Возвращаем объект шашки, данные которой были изменены
        return $piece;
    }

    public function finishGame()
    {
        //Инкремент сыгранных партий в матче
        $this->incGamesPlay();
        //Проверка завершения матча
        if ($this->getGamesPlay() < $this->getGamesCount()) {
            //Матч не завершен
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
     * Установка победителя в игре
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
        $this->getAnimation()->addAction($this->getCommand(), Core_Game_Checkers_Animation::EMPTY_ANIMATION);
    }

    /**
     * Получение текущего состояния игры для записи в историю (XML)
     *
     * @return string
     */
    public function saveHistory()
    {
        //Возвращаем данные игры для истории
        return $this->saveXml();
    }

    /**
     * Получение состояния игры в виде XML
     *
     * @return string
     */
    public function saveXml()
    {
        //Формирование XML
        $xml = new XMLWriter();
        $xml->openMemory();

        //Данные игровой доски
        $xml->writeElement('pieces', $this->getBoard()->__toString());

        //Возвращаем данные игры для истории
        return $xml->flush(false);
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
}