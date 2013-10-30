<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 15.06.12
 * Time: 12:11
 *
 * Класс описывающий игру Нарды
 */
class Core_Game_Backgammon extends Core_Game_Abstract
{

    /**
     * Название игры
     */
    const GAME_NAME = 'backgammon';

    /**
     * Объект игровой доски
     *
     * @var Core_Game_Backgammon_Board
     */
    protected $_board;

    /**
     * Объект истории анимаций
     *
     * @var Core_Game_Backgammon_Animation
     */
    protected $_animation;

    /**
     * Количество партий в матче
     *
     * @var int
     */
    protected $_gamesCount = 1;

    /**
     * Количество сыгранных партий в матче
     *
     * @var int
     */
    protected $_gamesPlay = 0;


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
     * Генерация начального состояния игрового стола
     *
     * @return void
     */
    public function generate()
    {
        //Создание объекта игровой доски
        $this->setBoard(new Core_Game_Backgammon_Board());
        //Создание объекта истории анимации
        $this->setAnimation(new Core_Game_Backgammon_Animation());
        //Установка времени на партию каждому игроку
        foreach($this->getPlayersContainer() as $player) {
            $player->setStartGametime($this->getGameTimeout());
        }
        //Установка права пераого хода (рандомно)
        //$this->_setFirstPlayer();
        //Установка первого хода игроку с белыми шашками
        foreach ($this->getPlayersContainer() as $player) {
            if ($player->getId() == Core_Game_Backgammon_Board::WHITE_PIECE) {
                $this->getPlayersContainer()->setActive($player);
                break;
            }
        }
        //Выбрасывание костей для первого хода
        $this->throwDice();
    }

    /**
     * Установка объекта игровой доски
     *
     * @param Core_Game_Backgammon_Board $board
     */
    public function setBoard(Core_Game_Backgammon_Board $board)
    {
        $this->_board = $board;
    }

    /**
     * Получение объекта игровой доски
     *
     * @return Core_Game_Backgammon_Board
     */
    public function getBoard()
    {
        return $this->_board;
    }

    /**
     * Установка объекта истории анимаций
     *
     * @param Core_Game_Backgammon_Animation $animation
     */
    public function setAnimation(Core_Game_Backgammon_Animation $animation)
    {
        $this->_animation = $animation;
    }

    /**
     * Получение объекта истории анимаций
     *
     * @return Core_Game_Backgammon_Animation
     */
    public function getAnimation()
    {
        return $this->_animation;
    }

    /**
     * Добавление игрока
     *
     * @param string   $sid      Идентификатор сессии пользователя
     * @param string   $name     Имя игрока
     * @param int|null $id       Цвет шашек игрока
     * @param int|null $runtime  Время игрока на ход
     * @param int|null $gametime Время игрока на партию
     * @param int|null $index    Порядковый номер пользователя в игре
     *
     * @return Core_Game_Players_Player
     */
    public function addPlayer($sid, $name, $id = null, $runtime = null, $gametime = null, $index = null)
    {
        //Проверка наличия цвета шашек
        if (null === $id) {
            //Определение случайного цвета шашек
            if (!rand(0, 1)) {
                $id = Core_Game_Backgammon_Board::WHITE_PIECE;
            } else {
                $id = Core_Game_Backgammon_Board::BLACK_PIECE;
            }
        }
        //Добавление игрока
        $player = parent::addPlayer($sid, $name, $id, $runtime, $gametime, $index);
        //Если у игрока белые шашки - у него право первого хода
        if ($id == Core_Game_Backgammon_Board::WHITE_PIECE) {
            $this->getPlayersContainer()->setActive($player);
        }

        return $player;
    }

    /**
     * Добавление оппонента
     *
     * @param string $sid  Идентификатор сессии пользователя
     * @param string $name Имя пользователя
     * @param int|null $runtime  Время игрока на ход
     * @param int|null $gametime Время игрока на партию
     * @param int|null $index    Порядковый номер пользователя в игре
     *
     * @return Core_Game_Players_Player
     */
    public function addOpponent($sid, $name, $runtime = null, $gametime = null, $index = null)
    {
        //Определение цвета шашек оппонента
        $currentPlayer = $this->getPlayersContainer()->getIterator()->getCurrentElement();
        if ($currentPlayer->getId() == Core_Game_Backgammon_Board::WHITE_PIECE) {
            $id = Core_Game_Backgammon_Board::BLACK_PIECE;
        } else {
            $id = Core_Game_Backgammon_Board::WHITE_PIECE;
        }
        //Установка оппонента за игровой стол
        return $this->addPlayer($sid, $name, $id, $runtime, $gametime, $index);
    }

    /**
     * Бросить игральные кости (сгенерировать новые случайные числа)
     *
     * @return void
     */
    public function throwDice()
    {
        //Очищаем данные игральных костей
        $this->getBoard()->getDice()->clear();
        //Установка новых случайных значений игровых костей
        $this->getBoard()->getDice()->setValues(rand(1,6), rand(1,6));

        //Сброс флага перемещения шашки с начальной позиции в рамках текущего хода (выброса костей)
        $this->getBoard()->setMoveFromStartCell(false);

        //Получаем объект активного игрока
        $activePlayer = $this->getPlayersContainer()->getActivePlayer();
        //Получаем позицию активного игрока за игровым столом
        $playerPos = $this->getPlayersContainer()->getPlayerPosition($activePlayer);
        //Проверка возможности хода у игрока
        $canMove = $this->getBoard()->canMove($activePlayer);
        //Запись действия в историю анимаций
        $this->getAnimation()->addThrowDiceAction(
            $this->getCommand(),
            $playerPos,
            $this->getBoard()->getDice()->__toString(),
            $canMove
        );

        //Если у игрока нет возможности хода, передаем ход следующему игроку
        if (!$canMove) {
            //Переключение активного игрока
            $this->getPlayersContainer()->switchActivePlayer();
            //Бросаем кости активного игрока
            $this->throwDice();
        }
    }

    /**
     * Перемещение шашки
     *
     * @param int $from Начальная позиция шашки
     * @param int $to   Конечная позиция шашки
     */
    public function move($from, $to)
    {
        //Получаем объект активного игрока
        $activePlayer = $this->getPlayersContainer()->getActivePlayer();
        //Перемещение шашки
        $this->getBoard()->move($from, $to, $activePlayer);
        //Запись действия в историю анимаций
        $this->getAnimation()->addMoveAction($this->getCommand(), $from, $to);
    }

    /**
     * Вывод шашки за пределы игровой доски
     *
     * @param int $from Позиция шашки
     *
     * @throws Core_Game_Backgammon_Exception
     */
    public function throwOut($from)
    {
        //Получаем объект активного игрока
        $activePlayer = $this->getPlayersContainer()->getActivePlayer();
        //Проверка возможности вывода шашек у игрока
        if (!$this->getBoard()->canThrowOut($activePlayer)) {
            throw new Core_Game_Backgammon_Exception('Player can not throw out piece');
        }
        //Вывод шашки
        $this->getBoard()->throwOut($from, $activePlayer);
        //Запись действия в историю анимаций
        $this->getAnimation()->addThrowOutAction($this->getCommand(), $from);
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
     * Получение количества сыгранных партий в матче
     *
     * @return int
     */
    public function getGamesPlay()
    {
        return $this->_gamesPlay;
    }

    /**
     * Проверка окончания партии
     *
     * @return bool
     */
    public function isFinish()
    {
        //Проверка наличия победителя
        if (false !== $this->getBoard()->getWinnerColor()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Метод обработки завершения партии
     *
     * @return void
     */
    public function finishGame()
    {
        //Инкремент сыгранных партий в матче
        $this->incGamesPlay();
        //Проверка завершения матча
        if ($this->getGamesPlay() < $this->getGamesCount()) {
            //Матч еще не завершен
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
        //Получение суммы выигрыша
        if (null === $winamount) {
            $winamount = count($this->getPlayersContainer()) * $this->getBet();
        }
        //TODO: комиссия выигрыша
        //Установка победителя
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
        $this->getAnimation()->addEmptyAction($this->getCommand());
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

        //Данные игральных костей
        $xml->writeElement('dice', $this->getBoard()->getDice());

        //Данные шашек
        $xml->startElement('pieces');
        //Получаем данные о шашках в виде строк
        $piecesStringData = $this->getBoard()->getPiecesString();
        //Формирование данных о шашках на игровой доске
        foreach($this->getPlaces() as $pos => $player) {
            if (!$player) {
                continue;
            }
            $xml->startElement('p');
            $xml->writeAttribute('pos', $pos);
            $xml->writeAttribute('color', $player->getId());
            $xml->text($piecesStringData[$player->getId()]);
            $xml->endElement();
        }
        $xml->endElement();

        //Отдаем XML
        return $xml->flush(false);
    }

    /**
     * Получение данных игры в виде XML
     *
     * @param bool $withDice   Показывать данные игральных костей
     *
     * @return string
     */
    public function saveXml($withDice = true)
    {
        //Формирование XML
        $xml = new XMLWriter();
        $xml->openMemory();

        //Данные игральных костей
        if ($withDice) {
            $xml->writeElement('dice', $this->getBoard()->getDice());
        }

        //Данные шашек
        $xml->startElement('pieces');
        //Получаем данные о шашках в виде строк
        $piecesStringData = $this->getBoard()->getPiecesString();
        //Формирование данных о шашках на игровой доске
        foreach($this->getPlaces() as $pos => $player) {
            if (!$player) {
                continue;
            }
            $xml->startElement('p');
            $xml->writeAttribute('pos', $pos);
            $xml->writeAttribute('color', $player->getId());
            $xml->text($piecesStringData[$player->getId()]);
            $xml->endElement();
        }
        $xml->endElement();

        //Отдаем XML
        return $xml->flush(false);
    }

    /**
     * Получение данных о текущем действии активного игрока в виде XML
     *
     * @return string
     */
    public function saveActionXml()
    {
        //Формирование XML
        $xml = new XMLWriter();
        $xml->openMemory();

        //Текущие действия, доступные активному игроку
        $activePlayer = $this->getPlayersContainer()->getActivePlayer();
        if ($activePlayer) {
            //Добавление блока информации о возможных действиях игрока
            $xml->startElement('action');
            //Цвет шашек игрока
            $color = $activePlayer->getId();
            //Проверка возможности вывода шашек за пределы игровой доски
            $canThrowout = $this->getBoard()->canThrowOut($activePlayer);
            //Объект расчета возможных движений шашек на игровой доске
            $movement = new Core_Game_Backgammon_Movement($this->getBoard());
            //Проход по всем ячейкам игровой доски с шашками игрока
            foreach($this->getBoard()->getPieces() as $cell => $pieces) {
                //Проверка вхождения номера ячейки в границы игровой области доски
                if ($cell < 1 || $cell > Core_Game_Backgammon_Board::BOARD_CELL_COUNT) {
                    continue;
                }
                //Проверка наличия шашек игрока в ячейке
                if (!count($pieces) || $this->getBoard()->getCellPiecesColor($cell) != $color) {
                    continue;
                }
                //Если пользователь выводит шашки, проверяем возможность вывода текущей шашки
                if ($canThrowout && $movement->canThrowout($cell, $color)) {
                    //Добавляем в XML информацию о возможности вывода шашки
                    $xml->startElement('throwout');
                    $xml->writeAttribute('cell', $cell);
                    $xml->endElement();
                }
                //Получение возможных позиций для перемещения текущей шашки
                $movementPositions = $movement->getPieceMovementPositions($cell, $color);
                if (count($movementPositions)) {
                    //Добавляем в XML информацию о возможности перемещения шашки
                    $xml->startElement('move');
                    $xml->writeAttribute('cell', $cell);
                    $xml->writeAttribute('values', implode(',', $movementPositions));
                    $xml->endElement();
                }

            }
            $xml->endElement();
        }

        //Отдаем XML
        return $xml->flush(false);
    }

    /**
     * Установка права на первый ход
     *
     * @return void
     */
    private function _setFirstPlayer()
    {
        //Рандомное получение позиции игрока для первого хода
        $firstPlayerIndex = rand(1, count($this->getPlayersContainer()));
        //Получение пользователя с правом на первый ход
        $firstPlayer = $this->getPlayersContainer()->getIterator()->getElement($firstPlayerIndex);

        //Установка курсора на первом игроке
        $this->getPlayersContainer()->getIterator()->setCurrentElement($firstPlayer);
        //Установка активного игрока и белый цвет шашек
        $this->getPlayersContainer()->setActive($firstPlayer);
        $firstPlayer->setId(Core_Game_Backgammon_Board::WHITE_PIECE);

        //Установка черного цвета шашек оппоненту
        $this->getPlayersContainer()->getIterator()->nextElement()->setId(Core_Game_Backgammon_Board::BLACK_PIECE);
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
