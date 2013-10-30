<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 09.06.12
 * Time: 14:22
 *
 * Класс игры Домино
 */
class Core_Game_Domino extends Core_Game_Abstract
{

    /**
     * Системное имя игры
     */
    const GAME_NAME = 'domino';

    /**
     * Начальное количество костей у игрока
     */
    const PLAYER_BONE_COUNT = 7;

    /**
     * Время на партию
     *
     * @var int
     */
    protected $_gameTimeout = 180;

    /**
     * Список игральных костей в резерве
     *
     * @var Core_Game_Domino_Bone_Array
     */
    protected $_reserve;

    /**
     * Ряд игровых костей на игровом столе
     *
     * @var Core_Game_Domino_Bone_Array
     */
    protected $_series;

    /**
     * Максимальное количествр очков, которое необходимо набрать для завершения игры
     *
     * @var int
     */
    protected $_maxPoints = 0;

    /**
     * Идентификатор сессии игрока победившего в розыгрыше
     *
     * @var string
     */
    protected $_winner;

    /**
     * Данные о первой игральной кости в ряде
     *
     * @var string
     */
    protected $_fbone;

    /**
     * Кость с которой необходимо начинать партию
     *
     * @var string
     */
    protected $_gbone;

    /**
     * Объект истории анимаций в игре
     *
     * @var Core_Game_Domino_Animation
     */
    protected $_animation;


    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->setReserve(new Core_Game_Domino_Bone_Array());
        $this->setSeries(new Core_Game_Domino_Bone_Array());
        $this->setAnimation(new Core_Game_Domino_Animation());
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
     * Установка массива костей в резерве
     *
     * @param Core_Game_Domino_Bone_Array $reserve
     */
    public function setReserve(Core_Game_Domino_Bone_Array $reserve)
    {
        $this->_reserve = $reserve;
    }

    /**
     * Получение массива костей в резерве
     *
     * @return Core_Game_Domino_Bone_Array
     */
    public function getReserve()
    {
        return $this->_reserve;
    }

    /**
     * Получение количества костей в резерве
     *
     * @return int
     */
    public function countReserve()
    {
        return count($this->getReserve());
    }

    /**
     * Установка объекта ряда костей на игровом столе
     *
     * @param Core_Game_Domino_Bone_Array $series
     */
    public function setSeries(Core_Game_Domino_Bone_Array $series)
    {
        $this->_series = $series;
    }

    /**
     * Получение объекта ряда костей на игровом столе
     *
     * @return Core_Game_Domino_Bone_Array
     */
    public function getSeries()
    {
        return $this->_series;
    }

    /**
     * Установка значения первой игральной кости в ряде
     *
     * @param string $bone
     */
    public function setFirstBone($bone)
    {
        $this->_fbone = $bone;
    }

    /**
     * Получение значения первой игральной кости в ряде
     *
     * @return string
     */
    public function getFirstBone()
    {
        return $this->_fbone;
    }

    /**
     * Установка значения кости, с которой необходимо совершить первый ход в партии
     *
     * @param Core_Game_Domino_Bone|string $bone
     */
    public function setGoBone($bone)
    {
        if ($bone instanceof Core_Game_Domino_Bone) {
            $bone = $bone->__toString();
        }
        $this->_gbone = $bone;
    }

    /**
     * Получение значения кости, с которой необходимо совершить первый ход в партии
     *
     * @return string
     */
    public function getGoBone()
    {
        return $this->_gbone;
    }

    /**
     * Установка максимального количества очков для завершения игры
     *
     * @param int $points
     */
    public function setMaxPoints($points)
    {
        $this->_maxPoints = $points;
    }

    /**
     * Получение максимального количества очков для завершения игры
     *
     * @return int
     */
    public function getMaxPoints()
    {
        return $this->_maxPoints;
    }

    /**
     * Установка объекта истории анимаций игры
     *
     * @param Core_Game_Domino_Animation $animation
     */
    public function setAnimation(Core_Game_Domino_Animation $animation)
    {
        $this->_animation = $animation;
    }

    /**
     * Получение объекта истории анимаций игры
     *
     * @return Core_Game_Domino_Animation
     */
    public function getAnimation()
    {
        return $this->_animation;
    }

    /**
     * Проверка игры до достижения максимального количества очков партии одним из игроков
     *
     * @return bool
     */
    public function isMatch()
    {
        return $this->_maxPoints > 0;
    }

    /**
     * Проверка возможности начать игру за игровым столом (изменить статус игры на PLAY)
     *
     * @return bool
     */
    public function canPlay()
    {
        //Проверка наличия всех игроков за игровым столом
        if (count($this->getPlayersContainer()) != $this->getMaxPlayersCount()) {
            return false;
        }

        //Проверка статусов игроков
        foreach($this->getPlayersContainer() as $player) {
            if (!$player->isPlay()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Генерация начального состояния игрового стола
     *
     * @return void
     */
    public function generate()
    {
        //Очистка данных
        $this->getReserve()->clear();
        $this->getSeries()->clear();
        $this->getAnimation()->clear();
        $this->setFirstBone(null);
        //Обновление данных игроков
        foreach($this->getPlayersContainer() as $player) {
            //Очистка массива игральных костей
            $player->getBoneArray()->clear();
            //Установка активности
            $player->setActive(false);
            //Установка таймаута партии
            $player->setStartGametime($this->getGameTimeout());
            //Если игра по партиям (не матч) обнуляем набранные игроком очки
            if (!$this->isMatch()) {
                $player->setPoints(0);
                $player->resetRememberPoints();
            }
        }

        //Формирование массива игровых костей
        for($i = 0; $i <= Core_Game_Domino_Bone::MAX_VALUE; $i ++) {
            for($j = $i; $j <= Core_Game_Domino_Bone::MAX_VALUE; $j ++) {
                //Создание игровой кости
                $bone = new Core_Game_Domino_Bone();
                $bone->setLeftValue($i);
                $bone->setRightValue($j);
                //Добавление игровой кости
                $this->getReserve()->addBone($bone);
            }
        }

        //Перемешиваем кости
        $this->getReserve()->shuffle();

        //Раздача костей игрокам
        foreach($this->getPlayersContainer() as $player) {
            for($i = 1; $i <= self::PLAYER_BONE_COUNT; $i++) {
                $this->deal($player);
            }
            //Проверка валидности раздачи
            if (!$this->_isValidFirstHand($player)) {
                //Генерируем данные партии заново
                $this->generate();
                return;
            }
        }

        //Определение и установка активного игрока
        $this->getPlayersContainer()->setActive($this->_defineActivePlayer());
    }

    /**
     * Добавление игрока
     *
     * @param string $sid Идентификатор сессии пользователя
     * @param string $name Имя игрока
     * @param mixed $id Идентификатор пользователя в игре
     * @param int|null $runtime Время игрока на ход
     * @param int|null $gametime Время игрока на партию
     * @param int|null $index Порядковый номер пользователя в игре
     * @return Core_Game_Players_Player
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
        $player = new Core_Game_Domino_Players_Player(array(
            'sid' => $sid,
            'id' => $id,
            'name' => $name,
            'runtime' => $runtime,
            'startGametime' => $gametime,
            'boneArray' => new Core_Game_Domino_Bone_Array()
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
     * Выдача кости игроку из резерва
     *
     * @param Core_Game_Domino_Players_Player $player
     *
     * @return Core_Game_Domino_Bone|false
     */
    public function deal(Core_Game_Domino_Players_Player $player)
    {
        //Проверка наличия костей в резерве
        if (!count($this->getReserve())) {
            return false;
        }
        //Извлечение костяшки из массива
        $bone = $this->getReserve()->pop();
        //Добавление костяшки игроку
        $player->getBoneArray()->addBone($bone);
        //Добавление действия в историю анимаций игры
        $this->getAnimation()->addAction(
            $this->getCommand(),
            Core_Game_Domino_Animation::DEAL,
            $this->getPlayersContainer()->getIterator()->getElementIndex($player),
            $bone->__toString()
        );

        return $bone;
    }

    /**
     * Добавление кости в ряд игрового стола
     *
     * @param Core_Game_Domino_Players_Player $player    Игрок подкинувший кость
     * @param Core_Game_Domino_Bone|string    $bone      Добавленная кость
     * @param int                             $placement Способ добавления, в начало или конец ряда
     *
     * @return void
     * @throws Core_Game_Domino_Exception
     */
    public function throwBone(Core_Game_Domino_Players_Player $player, $bone, $placement = Core_Game_Domino_Bone_Array::APPEND)
    {
        //Получение объекта игральной кости
        if (is_string($bone)) {
            $bone = new Core_Game_Domino_Bone($bone);
        }

        //Проверка наличия кости у игрока
        if (!$player->getBoneArray()->hasBone($bone)) {
            throw new Core_Game_Domino_Exception('Player has not bone ' . $bone, 3020, Core_Exception::USER);
        }
        //Удаление игральной кости из данных игрока
        $player->getBoneArray()->delBone($bone);

        //Получаем крайнее значение ряда костей
        if ($placement == Core_Game_Domino_Bone_Array::PREPEND) {
            $value = $this->getSeries()->getLeftValue();
        } else {
            $value = $this->getSeries()->getRightValue();
        }

        //Если это первый ход в партии, добавляем кость в ряд
        if (false === $value) {
            //Добавление игральной кости в ряд
            $this->getSeries()->addBone($bone);
            //Сохраняем данные первой кости в ряде (нужно для формирование истории розыгрыша)
            $this->setFirstBone($bone->__toString());
            //Добавляем действие в историю анимаций игры
            $this->_throwAnimation($player, $bone, $placement);
            return;
        }

        //Проверка наличия на подкинутой кости значения крайней кости ряда
        if (!$bone->hasValue($value)) {
            throw new Core_Game_Domino_Exception(
                'Bone does not match the values ​​of series extreme points',
                3021,
                Core_Exception::USER
            );
        }

        //Добавляем кость таким образом, чтобы совместить одинаковые значения крайней кости ряда и подкинутой кости
        if ($placement == Core_Game_Domino_Bone_Array::PREPEND && $value != $bone->getRightValue()) {
            //Поварачивем кость
            $bone->turn();
        } elseif ($placement == Core_Game_Domino_Bone_Array::APPEND && $value != $bone->getLeftValue()) {
            //Поварачивем кость
            $bone->turn();
        }
        $this->getSeries()->addBone($bone, $placement);
        //Добавляем действие в историю анимаций игры
        $this->_throwAnimation($player, $bone, $placement);
    }

    /**
     * Проверка состояния "рыба" за игровым столом
     *
     * @return bool
     */
    public function isFish()
    {
        //Проверка наличия костей в резерве
        if (count($this->getReserve())) {
            //Еще есть кости "на базаре", которые должен взять текущий игрок
            return false;
        }
        //Проверка наличия костей на столе
        if (!count($this->getSeries())) {
            return false;
        }

        //Проверка возможности у игроков подкинуть кость
        foreach($this->getPlayersContainer() as $player) {
            if ($this->_isPlayerCanThrowBone($player)) {
                //У пользователя есть возможность подкинуть кость
                return false;
            }
        }
        return true;
    }

    /**
     * Проверка окончания партии
     *
     * @return bool
     */
    public function isFinish()
    {
        //Проверяем наличие игрока без костей
        foreach($this->getPlayersContainer() as $player) {
            if (!count($player->getBoneArray())) {
                return true;
            }
        }
        //Проверка состояния "рыба"
        if ($this->isFish()) {
            return true;
        }

        return false;
    }

    /**
     * Установка победителя в игре
     *
     * @param Core_Game_Players_Player|string $player
     * @param int|null $winamount
     */
    public function setWinner($player, $winamount = null)
    {
        //Объект игрока
        $player = $this->getPlayersContainer()->getPlayer($player);
        if (!$player) {
            return;
        }
        //Установка победы
        $player->setStatus(Core_Game_Players_Player::STATUS_WINNER);
        //Установка суммы выигрыша
        if (null !== $winamount) {
            $player->setWinamount($winamount);
        }
    }

    /**
     * Установка проигравшего в игре
     *
     * @param Core_Game_Players_Player|string $player
     */
    /*public function setLoser($player)
    {
        //Установка поражения игроку
        parent::setLoser($player);

        //Получаем общую сумму выигрыша
        $generalWinAmount = $this->getBet() * $this->getMaxPlayersCount();

        //Проверка наличия игроков у которых 0 очков (они забирают весь выигрыш)
        foreach($this->getPlayersContainer())

        //Подсчет общей суммы очков
        $points = array();
        foreach($this->getPlayersContainer() as $element) {
            if ($element->getSid() == $player->getSid()) {
                continue;
            }
            $points[$element->getSid()] = $element->getPoints();
        }

        $pointsAmount = array_sum($points);
        //Определение процента выигрыша от суммы ставки для каждого игрока
        foreach($points as $sid => $amount) {
            //Процент от ставки
            if ($pointsAmount > 0) {
                $percent = 100 - ($amount / $pointsAmount * 100);
            } else {
                $percent = 0;
            }
            //Сумма выигрыша
            $winamount = round($generalWinAmount * $percent / 100);
            //Установка игроку суммы выигрыша и статус победителя
            $this->setWinner($sid, $winamount);
        }
    } */

    /**
     * Переключение активного игрока
     *
     * @return bool
     */
    public function switchActivePlayer()
    {
        //Переключаем активного игрока
        $this->getPlayersContainer()->switchActivePlayer();
        //Если игра завершена, дополнительных действий не требуется
        if ($this->isFinish()) {
            return;
        }

        //Получаем объект активного игрока
        $active = $this->getPlayersContainer()->getActivePlayer();
        //Проверка возможности активного игрока подкинуть кость
        while(!$this->_isPlayerCanThrowBone($active) && count($this->getReserve())) {
            //Добавляем кость игроку из резерва
            $this->deal($this->getPlayersContainer()->getActivePlayer());
        }
        //Если у игрока все еще нет возможности подкинуть кость, передаем ход следующему
        if (!$this->_isPlayerCanThrowBone($active)) {
            //Проверяем на состояание "рыба"
            if ($this->isFish()) {
                //Игра завершена
                return;
            }
            //Переключаемся на следующего игрока
            $this->switchActivePlayer();
        }
    }

    /**
     * Обработка окончания розыгрыша
     *
     * @param Core_Game_Domino_Players_Player|null $timeoutPlayer Игрок, у которого закончилось время партии
     *
     * @return void
     */
    public function finish(Core_Game_Domino_Players_Player $timeoutPlayer = null)
    {
        //Если в розыгрыше у игрока закончилось время на партию, он проиграл весь матч, деньги делятся попалам на оппонентов
        if (null !== $timeoutPlayer) {
            //Установка статуса проигрыша игроку
            $this->setLoser($timeoutPlayer);
            //Получаем сумму банска
            $bankAmount = $this->getBet() * $this->getMaxPlayersCount();
            //Делим банк попалам на оппонентов
            $amount = floor($bankAmount / ($this->getMaxPlayersCount() - 1));
            foreach($this->getPlayersContainer() as $player) {
                if ($player->getSid() != $timeoutPlayer->getSid()) {
                    $this->setWinner($player, $amount);
                }
            }
            //Игра завершена
            $this->setStatus(Core_Game_Abstract::STATUS_FINISH);
            return;
        }

        //Подсчет очков всех игроков
        $this->_scoring();
        //Проверка окончания матча
        //Получаем пользователя с наибольшим количеством очков
        $maxPoints = null;
        $loserSid  = null;
        foreach($this->getPlayersContainer() as $player) {
            if ($player->getPoints() > $maxPoints || $maxPoints === null) {
                $maxPoints = $player->getPoints();
                $loserSid = $player->getSid();
            }
        }
        //Проверяем достижение максимального количества очков
        if ($this->isMatch() && $maxPoints < $this->getMaxPoints()) {
            //Игра не завершена
            //Установка статуса окончания партии
            $this->setStatus(Core_Game_Abstract::STATUS_ENDGAME);
            return;
        }

        //Игра завершена
        $loser = $this->getPlayersContainer()->getPlayer($loserSid);
        //Установка выиграшей победителям
        $this->_defineWinners($loser);
        //Установка проигравшего
        $this->setLoser($loser);
        //Установка статуса окончания игры
        $this->setStatus(Core_Game_Abstract::STATUS_FINISH);
    }

    /**
     * Получение данных игрового стола в виде XML
     *
     * @param int|null $pos Позиция игрока за столом для которого формируются данные
     * @param bool $startState Флаг стартового состояния розыгрыша
     *
     * @return string
     */
    public function saveXml($pos = null, $startState = false)
    {
        //Формировние XML
        $xml = new XMLWriter();
        $xml->openMemory();

        //В стартовом состоянии необходимо отображать полный резерв и пользователей без игральных костей
        if ($startState) {
            //Максимальное значение одной стороны кости
            $maxBoneValue = Core_Game_Domino_Bone::MAX_VALUE;
            //Определение количества костей в игре
            $reserve = ($maxBoneValue + 1) * ($maxBoneValue + 2) / 2;
        } else {
            $reserve = count($this->getReserve());
        }
        //Формирование XML
        $xml->writeElement('reserve', $reserve);
        $xml->writeElement('bones', $this->getSeries());
        foreach ($this->getPlaces() as $position => $player) {
            $xml->startElement('user');
            $xml->writeAttribute('pos', $position);
            //В стартовом состоянии у игроков костей нет
            if (!$player || $startState) {
                $xml->endElement();
                continue;
            }
            if (null === $pos || $pos == $position) {
                $xml->text($player->getBoneArray());
            } else {
                $xml->text($player->getBoneArray()->showHiddenBones());
            }
            $xml->endElement();
        }

        //Отдаем XML
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
        $this->getAnimation()->addAction($this->getCommand(), Core_Game_Domino_Animation::EMPTY_ANIMATION);
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
        $xml->startElement('table');
        $xml->writeElement('first_item', $this->getFirstBone());
        $xml->writeRaw($this->saveXml());
        $xml->endElement();
        //Возвращаем данные игры для истории
        return $xml->flush(false);
    }

    /**
     * Проверка возможности игрока подкинуть кость в ряд
     *
     * @param Core_Game_Domino_Players_Player $player
     *
     * @return bool
     */
    private function _isPlayerCanThrowBone(Core_Game_Domino_Players_Player $player)
    {
        return $player->getBoneArray()->hasValue(array(
            $this->getSeries()->getLeftValue(),
            $this->getSeries()->getRightValue()
        ));
    }

    /**
     * Пересчет очков игроков
     *
     * @return void
     */
    private function _scoring()
    {
        //Идентификатор сессии победителя
        $winnerSid = null;
        //Минимальное количество очков набранных а текущем розыгрыше
        $minScore = null;
        //Флаг определения победителя
        $hasWinner = false;
        //Получаем сумму значений костей пользователей
        foreach($this->getPlayersContainer() as $player) {
            //Установка очков пользователю
            $score = $player->getBoneArray()->getAmount();
            $player->addPoints($score);

            //Проверяем игрока на минимальное количество очков в розыгрыше
            if (!$hasWinner && ($minScore === null || $score < $minScore)) {
                //Установка значения минимальной суммы очков
                $minScore = $score;
                //Запоминаем идентификатор сессии игрока с минимальным количеством очков
                $winnerSid = $player->getSid();
            }
            //Если у игрока нет костей на руках - он выиграл
            if (!count($player->getBoneArray())) {
                //Запоминаем идентификатор сессии победителя
                $winnerSid = $player->getSid();
                //Обнуление "запомненных" очков
                $player->resetRememberPoints();
                //Установка флага определения победителя
                $hasWinner = true;
            }
        }

        //Установка победителя текущего розыгрыша
        $this->_setWinner($winnerSid);
    }

    /**
     * Установка игрока - победителя розыгрыша
     *
     * @param Core_Game_Players_Player|string $player
     */
    private function _setWinner($player)
    {
        if ($player instanceof Core_Game_Domino_Players_Player) {
            $player = $player->getSid();
        }

        $this->_winner = $player;
    }

    /**
     * Получение игрока - победителя розыгрыша
     *
     * @return bool|Core_Game_Players_Player
     */
    private function _getWinner()
    {
        if (null !== $this->_winner) {
            return $this->getPlayersContainer()->getPlayer($this->_winner);
        }
        return false;
    }

    /**
     * Добавление анимации добавления игральной кости в ряд
     *
     * @param Core_Game_Domino_Players_Player $player    Объект игрока
     * @param Core_Game_Domino_Bone           $bone      Объект игральной кости
     * @param int                             $placement Позиция добавления кости
     */
    private function _throwAnimation(Core_Game_Domino_Players_Player $player, Core_Game_Domino_Bone $bone, $placement)
    {
        //Добавляем действие в историю анимаций игры
        if ($placement == Core_Game_Domino_Bone_Array::PREPEND) {
            $side = Core_Game_Domino_Animation::SIDE_LEFT;
        } else {
            $side = Core_Game_Domino_Animation::SIDE_RIGHT;
        }
        $this->getAnimation()->addAction(
            $this->getCommand(),
            Core_Game_Domino_Animation::THROWIN,
            $this->getPlayersContainer()->getIterator()->getElementIndex($player),
            $bone->__toString(),
            $side
        );
    }

    /**
     * Определение текущего активного игрока
     *
     * @return Core_Game_Domino_Players_Player
     */
    private function _defineActivePlayer()
    {
        //Если это не начало розыгрыша, отдаем текущего активного игрока
        if (count($this->getSeries())) {
            return $this->getPlayersContainer()->getActivePlayer();
        }

        //Если есть победитель предыдущего розыгрыша, он ходит первым (в матчевой игре)
        if ($this->isMatch() && $this->_getWinner()) {
            $this->setGoBone(null);
            return $this->_getWinner();
        }

        //Первый ход определяется по наличию у игрока парной кости с наименьшим значением, кроме дубль пусто
        $minDoubleBone = null;
        $minDoublePlayer = null;
        //Если нет дублей первый ходит с дубль пусто 0-0
        $zeroPlayer = null;
        //Если нет дублей и нет 0-0, первым ходит игрок с костью 5-6 и т.д. по убыванию
        $maxBone = null;
        $maxBonePlayer = null;

        //Проход по всем костям всех игроков
        foreach($this->getPlayersContainer() as $player) {
            foreach($player->getBoneArray() as $bone) {
                //Сумма значений кости
                $boneAmount = $bone->getAmount();
                //Если сумма нулевая, запоминаем, пропускаем
                if ($boneAmount <= 0) {
                    $zeroPlayer = $player->getSid();
                    continue;
                }
                //Проверка парного значения с минимальной суммой
                if ($bone->isDouble() && (null === $minDoubleBone || $boneAmount < $minDoubleBone->getAmount())) {
                    //Установка минимальной суммы игральных костей с дублем
                    $minDoubleBone = $bone;
                    //Установка пользователя с минимальным дублем (кроме 0-0)
                    $minDoublePlayer = $player->getSid();
                } elseif (!$bone->isDouble() && (null === $maxBone || $boneAmount > $maxBone->getAmount())) {
                    //Установка максимальной суммы кости не дубль
                    $maxBone = $bone;
                    //Установка пользователя с максимальной суммой кости без дубля
                    $maxBonePlayer = $player->getSid();
                }
            }
        }

        //Проверка наличия игрока с наименьшим дублем (кроме 0-0)
        if ($minDoublePlayer) {
            $activePlayer = $minDoublePlayer;
            //Установка кости для первого хода
            Zend_Registry::get('log')->debug($minDoubleBone->getValue());
            if ($minDoubleBone->getValue() == '1:1') {
                $this->setGoBone($minDoubleBone);
            }
        }
        //Проверка наличия игрока с дублем пусто (0-0)
        elseif ($zeroPlayer) {
            $activePlayer = $zeroPlayer;
            //Установка кости для первого хода
            //$this->setGoBone('0:0');
        }
        //Отдаем игрока, у которого есть кость с наибольшим значением
        else {
            $activePlayer = $maxBonePlayer;
            //Установка кости для первого хода
            //$this->setGoBone($maxBone);
        }

        return $this->getPlayersContainer()->getPlayer($activePlayer);
    }

    /**
     * Определение победителей матча/розыгрыша
     *
     * @param Core_Game_Domino_Players_Player $loser Объект проигравшего игрока "козла"
     */
    private function _defineWinners(Core_Game_Domino_Players_Player $loser)
    {
        //Плучение общей суммы банка
        $bankAmount = $this->getBet() * $this->getMaxPlayersCount();

        //Получение списка игроков, у которых ноль очков
        $zeroPointPlayers = array();
        foreach($this->getPlayersContainer() as $player) {
            if ($player->getPoints() == 0) {
                $zeroPointPlayers[] = $player->getSid();
            }
        }
        //Если есть игроки с нулевым балансом делим между ними выигрышь попалам,
        //всем остальным пользователям устанавливаем проигрыш
        if (count($zeroPointPlayers)) {
            $amount = floor($bankAmount / count($zeroPointPlayers));
            foreach($this->getPlayersContainer() as $player) {
                if (in_array($player->getSid(), $zeroPointPlayers)) {
                    //Победитель
                    $this->setWinner($player, $amount);
                } else {
                    //Проигравший
                    $this->setLoser($player);
                }
            }
        } else {
            //Если игроков с нулевым количеством очков нет, делим выигрыш обратнопропорционально набранным очкам
            //Получаем сумму всех очков пользователей
            $pointsAmount = 0;
            foreach($this->getPlayersContainer() as $player) {
                if ($player->getSid() != $loser->getSid()) {
                    $pointsAmount += $player->getPoints();
                }
            }
            //Установка сумм выигрыша победителям
            foreach($this->getPlayersContainer() as $player) {
                if ($player->getSid() != $loser->getSid()) {
                    //Получение процента от выигрыша для игрока
                    $percent = 100 - ($player->getPoints() / $pointsAmount * 100);
                    //Получаем сумму выигрыша
                    $amount = floor($pointsAmount * $percent / 100);
                    //Установка выигрыша
                    $this->setWinner($player, $amount);
                }
            }
        }
    }

    /**
     * Проверка валидности костей на руках у игрока при первой раздаче
     *
     * @param Core_Game_Domino_Players_Player $player
     *
     * @return bool
     */
    private function _isValidFirstHand(Core_Game_Domino_Players_Player $player)
    {
        //Проход по всем костям в массиве (на руках у игрока)
        //Подсчет количества дублей и значений
        $doublesCount = 0;
        $valuesCount = array_fill(0, 7, 0);
        foreach($player->getBoneArray() as $bone) {
            //Проверка дубля
            if ($bone->isDouble()) {
                //Инкремент количества дублей и проверка превышения макс. количества
                if (++$doublesCount >= 5) {
                    //Превышено допустимое количество дублей на руках при первой раздаче
                    return false;
                }
            }
            //Проверка количества значений на костях
            if (++$valuesCount[$bone->getLeftValue()] >= 6) {
                //Превышено количество одинаковых значений
                return false;
            }
            if (++$valuesCount[$bone->getRightValue()] >= 6) {
                //Превышено количество одинаковых значений
                return false;
            }
        }

        return true;
    }
}
