<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 11:23
 *
 * Реализация игры "Дурак"
 */
class Core_Game_Durak extends Core_Game_Abstract
{

    /**
     * Системное имя игры
     */
    const GAME_NAME = 'durak';

    /**
     * Количество карт на руках игрока в начале партии
     */
    const PLAYER_CARDS_COUNT = 6;

    /**
     * Колода карт
     *
     * @var Core_Game_Durak_Cards_Pack
     */
    protected $_pack;

    /**
     * Объект массива карт в отбое
     *
     * @var Core_Game_Durak_Cards_Array
     */
    protected $_pulldown;

    /**
     * Объект данных текущего розыгрыша
     *
     * @var Core_Game_Durak_Process
     */
    protected $_process;

    /**
     * Текущее количество очков за выход из игры
     *
     * @var float
     */
    protected $_points = 1;

    /**
     * Объект истории обновления данных за игровым столом (для поддержки анимции на стороне клиента)
     *
     * @var Core_Game_Durak_Animation
     */
    protected $_animation;

    /**
     * Временные данные розыгрыша для сохранения в истории после очистки (сохранение в истории происходит после очистки)
     *
     * @var string
     */
    protected $_processHistory;

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
    protected $_gamesPlay;


    /**
     * Создание игры
     */
    public function __construct()
    {
        //Вызов родительского конструктора
        parent::__construct();

        //Создание и установка контейнера пользователей
        $this->setPlayersContainer(new Core_Game_Durak_Players_Container($this->_playersCount));
        //Создание и установка колоды карты
        $this->setPack(new Core_Game_Durak_Cards_Pack());
        //Создание и установка отбоя
        $this->setPulldown(new Core_Game_Durak_Cards_Array());
        //Создание объекта анимации действий
        $this->setAnimation(new Core_Game_Durak_Animation());
    }

    /**
     * Magic method __wakeup
     *
     * @return void
     */
    public function __wakeup()
    {
        parent::__wakeup();

        if (null !== $this->_process) {
            $this->getProcess()->setGame($this);
        }
    }

    /**
     * Формирование данных новой партии
     */
    public function generate()
    {
        //Создание новой колоды карт
        $this->setPack(new Core_Game_Durak_Cards_Pack());
        //Обнуление отбоя
        $this->setPulldown(new Core_Game_Durak_Cards_Array());
        //Очистка данных розыгрыша
        $this->clearProcess();
        //Установка начальной суммы очков за завершение розыгрыша
        $this->setPoints(1);
        //Очистка данных истории (анимации)
        $this->getAnimation()->clear();
        //Обновление данных игроков
        foreach($this->getPlayersContainer() as $player) {
            //Изменяем состояние игрока
            $player->setActive(false);
            $player->setPlay();
            //Обновление таймера
            $player->setStartGametime($this->getGameTimeout());
            //Очистка списка карт игрока
            $player->getCardArray()->clear();
        }
        //Раздача карт (по одной каждому пользователю)
        for($i = 1; $i <= self::PLAYER_CARDS_COUNT; $i++) {
            foreach($this->getPlayersContainer() as $player) {
                $this->dealCards($player, 1);
            }
        }
        //Проверка корректности раздачи карт
        foreach($this->getPlayersContainer() as $player) {
            if ($player->getCardArray()->isTooMatchOfSameSuit()) {
                //Заново генерируем данные стола
                $this->generate();
                return;
            }
        }
        //Инициализация розыгрыша
        $this->_initProcess();
        Zend_Registry::get('log')->debug('GENERATE HAS DONE');
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
     * Проверка игры в нексколько партий (матч)
     *
     * @return bool
     */
    public function isMatch()
    {
        return $this->getGamesCount() > 1;
    }

    /**
     * Получение объекта контейнера игроков
     *
     * @return Core_Game_Durak_Players_Container|Core_Game_Durak_Players_Player[]
     */
    public function getPlayersContainer()
    {
        return parent::getPlayersContainer();
    }

    /**
     * Установка объекта колоды карт
     *
     * @param Core_Game_Durak_Cards_Pack $pack
     * @return Core_Game_Durak
     */
    public function setPack(Core_Game_Durak_Cards_Pack $pack)
    {
        $this->_pack = $pack;
        return $this;
    }

    /**
     * Получение объекта колоды карт
     *
     * @return Core_Game_Durak_Cards_Pack
     */
    public function getPack()
    {
        return $this->_pack;
    }

    /**
     * Установка объекта массива карт в отбое
     *
     * @param Core_Game_Durak_Cards_Array $cards
     * @return Core_Game_Durak
     */
    public function setPulldown(Core_Game_Durak_Cards_Array $cards)
    {
        $this->_pulldown = $cards;
        return $this;
    }

    /**
     * Получение объекта массива карт в отбое
     *
     * @return Core_Game_Durak_Cards_Array
     */
    public function getPulldown()
    {
        return $this->_pulldown;
    }

    /**
     * Установка объекта розыгрыша в игре
     *
     * @param Core_Game_Durak_Process $process
     * @return Core_Game_Durak
     */
    public function setProcess(Core_Game_Durak_Process $process)
    {
        $this->_process = $process;
        return $this;
    }

    /**
     * Получение объекта розыгрыша в игре
     *
     * @return Core_Game_Durak_Process
     */
    public function getProcess()
    {
        if (null === $this->_process && $this->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            //Инициализация объекта розыгрыша
            $this->_initProcess();
        }
        return $this->_process;
    }

    /**
     * Установка текущего количества очков за выход из игры
     *
     * @param float $points
     * @return Core_Game_Durak
     */
    public function setPoints($points)
    {
        $this->_points = $points;
        return $this;
    }

    /**
     * Получение текущего количества очков за выход из игры
     *
     * @return float
     */
    public function getPoints()
    {
        return $this->_points;
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
     * Установка количества сыгранных партий
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
     * Получение количества сыгранных партий
     *
     * @return int
     */
    public function getGamesPlay()
    {
        return $this->_gamesPlay;
    }

    /**
     * Установка объекта истории изменения данных игрового стола (для поддержки анимации на стороне клиента)
     *
     * @param Core_Game_Durak_Animation $animation
     * @return Core_Game_Durak
     */
    public function setAnimation(Core_Game_Durak_Animation $animation)
    {
        $this->_animation = $animation;
        return $this;
    }

    /**
     * Получение объекта истории изменения данных игрового стола (для поддержки анимации на стороне клиента)
     *
     * @return Core_Game_Durak_Animation
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
     * @param int|null $id       Идентификатор игрока (позиция за игровым столом, то же что и $index)
     * @param int|null $runtime  Время игрока на ход
     * @param int|null $gametime Время игрока на партию
     * @param int|null $index    Порядковый номер пользователя в игре
     *
     * @return Core_Game_Durak_Players_Player|Core_Game_Players_Player
     */
    public function addPlayer($sid, $name, $id = null, $runtime = null, $gametime = null, $index = null)
    {
        //Установка времени хода и игры по умолчанию
        if (null === $runtime) {
            $runtime = $this->getRunTimeout();
        }
        if (null === $gametime) {
            $gametime = $this->getGameTimeout();
        }
        //Установка позиции игрока по умолчанию
        if (null === $id) {
            //Получаем количество игроков
            $playerCount = count($this->getPlayersContainer());
            //Инкремент идентификатора
            $id = $playerCount + 1;
        }

        //Создание объекта игрока
        $player = new Core_Game_Durak_Players_Player(array(
            'sid' => $sid,
            'id' => $id,
            'name' => $name,
            'runtime' => $runtime,
            'startGametime' => $gametime,
            'cardArray' => new Core_Game_Durak_Cards_Array()
        ));

        //Добавление игрока
        $this->getPlayersContainer()->addPlayer($player, $id);

        //Возвращаем объект добавленного игрока
        return $player;
    }

    /**
     * Добавление оппонента в игру
     *
     * @param string $userSid
     * @param string $name
     * @param null $position
     *
     * @return Core_Game_Durak_Players_Player
     */
    public function addOpponent($userSid, $name, $position = null)
    {
        return $this->addPlayer($userSid, $name, $position);
    }

    /**
     * Подкинуть карты в розыгрыш
     *
     * @param Core_Game_Durak_Players_Player|string $player Игрок, подкидывающий карты
     * @param Core_Game_Durak_Cards_Card[]|array|string $cards Список карт
     * @throws Core_Game_Durak_Exception
     */
    public function throwCards($player, $cards)
    {
        //Преобразование входящих данных
        if (is_scalar($player)) {
            $player = $this->getPlayersContainer()->getPlayer($player);
        }
        if (is_scalar($cards)) {
            $cards = explode(',', $cards);
        }

        //Получение позиции пользователя
        $position = $this->getPlayersContainer()->getIterator()->getElementIndex($player);

        //Флаг добавления хоть одной карты
        $added = false;
        foreach($cards as $card) {
            //Получение объекта карты
            if (is_string($card)) {
                $card = Core_Game_Durak_Cards_Card::create($card);
            }

            //Попытка добавления карты в розыгрыш
            try {
                //Добавление карты в розыгрышь
                $this->getProcess()->addCard($player, $card);
                //Добавление действия в историю
                $this->getAnimation()->addAction($this->getCommand(), Core_Game_Durak_Animation::THROWIN, $position, $card->__toString());
                $added = true;
            } catch (Core_Game_Durak_Exception $e) {
                //Если не была добавлена ни одна карта - выбрасываем исключение
                if (!$added) {
                    throw $e;
                } elseif(Zend_Registry::getInstance()->isRegistered('log')) {
                    Zend_Registry::get('log')->err($e);
                }
            }
        }

        //Установка активности отбивающегося (в случае, если отбивающийся не объявил о взятии карт)
        if (!$this->getProcess()->isLose()) {
            $this->getPlayersContainer()->setActive($this->getPlayersContainer()->getDefenderPlayer());
        }

    }

    /**
     * Бить карту
     *
     * @param Core_Game_Durak_Cards_Card|string $card1 Битая карта
     * @param Core_Game_Durak_Cards_Card|string $card2 Бьющая карта
     */
    public function beatOffCard($card1, $card2)
    {
        //Преобразование входящих данных
        if (is_string($card1)) {
            $card1 = Core_Game_Durak_Cards_Card::create($card1);
        }
        if (is_string($card2)) {
            $card2 = Core_Game_Durak_Cards_Card::create($card2);
        }

        //Получение позиции отбивающегося игрока
        $defender = $this->getPlayersContainer()->getDefenderPlayer();
        $position = $this->getPlayersContainer()->getIterator()->getElementIndex($defender);

        //Отбиваем карту
        $this->getProcess()->beatOffCard($card1, $card2);

        //Запись действия в историю
        $cards = array($card1->__toString(), $card2->__toString());
        $this->getAnimation()->addAction($this->getCommand(), Core_Game_Durak_Animation::BEATOFF, $position, $cards);

        //Проверка отбития всех карт
        if ($this->getProcess()->isDefend()) {
            //Убираем активность отбивающегося
            $this->getPlayersContainer()->getDefenderPlayer()->setActive(false);
            //старт таймера розыгрыша
            $this->getProcess()->startTimer();
        }
    }

    /**
     * Отказ игрока подкинуть карту в розыгрыш
     *
     * @param Core_Game_Durak_Players_Player $player
     * @throws Core_Exception
     */
    public function throwRefuse(Core_Game_Durak_Players_Player $player)
    {
        //Проверяем не является ли пользователь отбивающимся игроком
        if ($this->getProcess()->getDefender() == $player) {
            throw new Core_Exception('Defender player can\'t refuse action', 3009, Core_Exception::USER);
        }

        //Проверка теущего состояния игрока
        if (!$player->isPlay()) {
            throw new Core_Exception('Refuse failed. Player is not in the game', 3010, Core_Exception::USER);
        }

        //Проверка первого хода атакующего
        $processCardsCount = count($this->getProcess());
        if ($player == $this->getPlayersContainer()->getAtackPlayer() && !$processCardsCount) {
            throw new Core_Exception('Refuse failed. Player is attacker', 3011, Core_Exception::USER);
        }

        $this->getProcess()->throwRefuse($player);
    }

    /**
     * Очистка данных розыгрыша
     *
     * @return void
     */
    public function clearProcess()
    {
        //Очищаем данные розыгрыша
        $this->_process = null;
    }

    /**
     * Выдача карт пользователю
     *
     * @param Core_Game_Durak_Players_Player $player
     * @param int|null $cardsCount Указывает количество карт для раздачи игроку, null - определение количества автоматически
     *
     * @return bool
     */
    public function dealCards(Core_Game_Durak_Players_Player $player, $cardsCount = null)
    {
        //Проверка наличия карт в колоде
        if (!count($this->getPack())) {
            return false;
        }

        //Необходимое количество карт пользователю
        if (null === $cardsCount) {
            $cardsCount =  abs(self::PLAYER_CARDS_COUNT - count($player->getCardArray()));
        }

        //Проверка превышения допустимого количества карт на руках у игрока
        if (count($player->getCardArray()) + $cardsCount > self::PLAYER_CARDS_COUNT) {
            return false;
        }

        //Проверка наличия в колоде необходимого количества карт
        if (count($this->getPack()) < $cardsCount) {
            //Раздача оставшегося количество карт в колоде
            $cardsCount = count($this->getPack());
        }

        //Определение позиции пользователя для записи в историю
        $position = $this->getPlayersContainer()->getIterator()->getElementIndex($player);

        //Выдача карт из колоды
        for($i = 1; $i <= $cardsCount; $i++) {
            //Передача карты из колоды пользователю
            $card = $this->getPack()->pop();
            $player->getCardArray()->add($card);
            //Запись действия в историю
            $this->getAnimation()->addAction($this->getCommand(), Core_Game_Durak_Animation::DEAL, $position, $card->__toString());
        }
    }

    /**
     * Проверка возможности начать игру за игровым столом (изменить статус игры на PLAY)
     *
     * @return bool
     */
    public function canPlay()
    {
        //Получаем количество мест за игровым столом
        $places = $this->getMaxPlayersCount();
        //Проверка заполенения всех мест за игровым столом
        if (count($this->getPlayersContainer()) != $places) {
            return false;
        }

        //Проверка состояния игроков
        $canPlay = true;
        foreach($this->getPlayersContainer() as $player) {
            if (!$player->isPlay()) {
                $canPlay = false;
                break;
            }
        }

        return $canPlay;
    }

    /**
     * Установка победителя в игре
     *
     * @overide
     * @param Core_Game_Players_Player|string $player
     * @param int $winamount
     */
    public function setWinner($player, $winamount = null)
    {
        if (is_scalar($player)) {
            $player = $this->getPlayersContainer()->getPlayer($player);
        }

        if ($player) {
            //Установка победителя
            $player->setStatus(Core_Game_Players_Player::STATUS_WINNER);
            //Установка суммы победителя
            $player->setWinamount(intval($winamount));
        }
    }

    /**
     * Установка статуса ничьи для всех игроков
     *
     * @param Core_Game_Players_Player|string $player
     * @param int|null $winamount Сумма выигрыша игрока
     *
     * @return void
     */
    public function setDrawPlayer($player, $winamount = null)
    {
        if (is_scalar($player)) {
            $player = $this->getPlayersContainer()->getPlayer($player);
        }

        if ($player) {
            //Установка побудителя
            $player->setStatus(Core_Game_Players_Player::STATUS_DRAW);
            //Установка суммы победителя
            $player->setWinamount(intval($winamount));
        }
    }

    /**
     * Завершение партии/матча
     *
     * @param bool $finishMatch
     * @return void
     */
    public function finishGame($finishMatch = false)
    {
        //Проверка окончания матча
        if (!$finishMatch && $this->isMatch() && $this->getGamesCount() > $this->getGamesPlay()) {
            //Матч не окончен
            //Установка статуса окончания партии
            $this->setStatus(Core_Game_Abstract::STATUS_ENDGAME);
            return;
        }

        //Матч окончен, подсчитывем
        $playersProfit = $this->getPlayersProfit();
        //Проверка наличия проигравшего
        if (false === array_search(0, $playersProfit)) {
            $isLose = false;
        } else {
            $isLose = true;
        }
        //Устанавливаем суммы выигрышей игрокам
        foreach($playersProfit as $sid => $amount) {
                //Установка результата игры игрока
                if ($amount > 0) {
                    if ($isLose) {
                        $this->setWinner($sid, $amount);
                    } else {
                        $this->setDrawPlayer($sid, $amount);
                    }
                } else {
                    $this->setLoser($sid);
                }
        }

        //Изменение состояния всех игроков
        foreach($this->getPlayersContainer() as $player) {
            $player->setPlay(false);
        }

        //Установка статуса завершения игры
        $this->setStatus(Core_Game_Abstract::STATUS_FINISH);
    }

    /**
     * Получение суммы выигрыша игрока
     *
     * @param Core_Game_Durak_Players_Player|string $player
     * @return int
     */
    public function getPlayerProfit($player)
    {
        if (is_scalar($player)) {
            $player = $this->getPlayersContainer()->getPlayer($player);
        }

        //Получаем суммы выигрышей всех игроков
        $playersProfit = $this->getPlayersProfit();
        //Возвращаем сумму выигрыша указанного пользователя
        return $playersProfit[$player->getSid()];
    }

    /**
     * Получение сумм выигрышей игроков
     *
     * @return array
     */
    public function getPlayersProfit()
    {
        //Текущий банк
        $bank = $this->getBet() * count($this->getPlayersContainer());
        //Сумма выигрыша каждого игрока
        $result = array();
        //Определяем минимальное и макисмальное значение набранных очков игроками
        $min = $max = null;
        foreach($this->getPlayersContainer() as $player) {
            //Проверка на минимальное значение
            if (null === $min || $player->getPoints() < $min) {
                $min = $player->getPoints();
            }
            //Проверка на максимальное значение
            if (null === $max || $player->getPoints() > $max) {
                $max = $player->getPoints();
            }
            //Создание записи о сумме выигрыша игрока
            $result[$player->getSid()] = 0;
        }
        //В дураке могут быть только два призовых места, поэтому формируем массив из 1 либо 2 элементов,
        //ключи которого будут соответствовать призовому месту, а значения - список игроков, занявших эти места
        $places = array();
        $firstIndex = 0;
        foreach($this->getPlayersContainer() as $player) {
            if ($player->getPoints() == $max) {
                //Набрано максимальное количество очков - 1 место
                $places[$firstIndex][] = $player->getSid();
            } elseif ($player->getPoints() != $min) {
                //Набрано не наименьшее количество очков, но и не максимальное, 2 место
                $places[$firstIndex + 1][] = $player->getSid();
            }
        }
        //Определяем процент выигрыша для первого места
        if (count($places > 1)) {
            //Разделение выигрыша на 1 и 2 места. 1 место - 80%, 2 место - 20%
            $winPercent = 80;
        } else {
            //Весь выигрышь для первого места
            $winPercent = 100;
        }
        //Подсчет выигрыша игроков
        foreach($places as $players) {
            if ($winPercent <= 0)
                continue;
            //Сумма выигрыша
            $winAmount = $bank * ($winPercent/count($players)) / 100;
            //Установка суммы выигрыша игрокам
            foreach($players as $sid) {
                $result[$sid] = $winAmount;
            }
            //Процент выигрыша для следующего призового места
            $winPercent = 100 - $winPercent;
        }

        return $result;

        //Формирование ассоциативного массива, количество очков -> список игроков
        $pointPlayers = array();
        foreach($this->getPlayersContainer() as $player) {
            $pointPlayers[$player->getPoints()][] = $player->getSid();
        }
        //Сортировка по количеству очков
        ksort($pointPlayers, SORT_NUMERIC);
        //Определяем процент выигрыша за первое место
        if (count($this->getPlayersContainer()) == 3) {
            $winPercent = 80;
        } else {
            $winPercent = 100;
        }
        //Массив игроков и их суммы выигрыша
        $result = array();
        //Поочереди достаем список игроков из конца массива (от победителя до проигравшего)
        while($players = array_pop($pointPlayers)) {
            //Подсчет суммы выигрыша
            $amount = $bank * ($winPercent/count($players)) / 100;
            //Записываем выигрыш пользователей
            foreach($players as $sid) {
                $result[$sid] = $amount;
            }
            //Определение процента выигрыша следующего места
            if ($winPercent == 80) {
                $winPercent = 20;
            } else {
                $winPercent = 0;
            }
        }

        //Возвращаем список игроков и их суммы выигрыша
        return $result;
    }

    /**
     * Получение данных игрового стола в виде XML
     *
     * @param int|null $pos Порядковый номер пользователя для которго необходимо показывать данные розыгрыша
     * @param bool $showPlayersCards
     * @param bool $fullPack Флаг отображения полной колоды при первой раздаче карт (для корректного отображения анимации во флэше)
     * @return string
     */
    public function saveXml($pos = null, $showPlayersCards = true, $fullPack = true)
    {
        //Формировние XML
        $xml = new XMLWriter();
        $xml->openMemory();

        //Получаем данные игрока с указанным местом за столом
        $iterator = $this->getPlayersContainer()->getIterator();
        $currentPlayer = $iterator->getElement($pos);

        //Получаем данные розыгрыша
        $process = $this->getProcess();

        //Данные колоды
        $xml->startElement('pack');
        $xml->writeAttribute('trump', $this->getPack()->getTrump());
        $xml->writeAttribute('show', $this->getPack()->getTrumpCard());
        //Если первый розыгрыш, передаем количество карт до раздаичи (полнцю колоду)
        if ($process && $process->isFirstProccess() && $fullPack) {
            $xml->text(Core_Game_Durak_Cards_Pack::CARDS_COUNT);
        } else {
            $xml->text(count($this->getPack()));
        }
        $xml->endElement();

        //Данные отбоя
        $xml->startElement('pulldown');
        $xml->writeAttribute('count', count($this->getPulldown()));
        $xml->endElement();

        //Данные игроков
        foreach($this->getPlaces() as $pos => $player) {
            $xml->startElement('user');
            $xml->writeAttribute('pos', $pos);
            //Проверка необходимости отображать карты пользователя
            if ($player && $showPlayersCards) {
                if (!$currentPlayer || $currentPlayer == $player) {
                    //Показываем открытые карты
                    $xml->text($player->getCardArray());
                } else {
                    //Показываем закрытые карты
                    $xml->text($player->getCardArray()->showHiddenCards());
                }
            }
            $xml->endElement();
        }

        //Данные карт в розыгрыше
        $xml->writeElement('process', $process);

        //Возвращаем данные розыгрыша в виде XML
        return $xml->flush(false);
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
        $this->getAnimation()->addAction($this->getCommand(), Core_Game_Durak_Animation::EMPTY_ANIMATION);
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
        $xml->startElement('cards');
        $xml->writeRaw($this->saveXml(null, true, false));
        $xml->endElement();
        //Возвращаем данные игры для истории
        $result = $xml->flush(false);

        return $result;
    }

    /**
     * Инициализация объекта розыгрыша
     */
    protected function _initProcess()
    {
        //Получаем количество игроков за столом
        $playersCount = count($this->getPlayersContainer());
        //Инициализация объекта соответствующего розыгрыша (одиночный или на пары)
        if ($playersCount == 4) {
            $this->_process = new Core_Game_Durak_Process_Doubles($this);
        } else {
            $this->_process = new Core_Game_Durak_Process_Single($this);
        }
        //Очистка временных данных предыдущего розыгрыша
        $this->_processHistory = null;
    }
}
