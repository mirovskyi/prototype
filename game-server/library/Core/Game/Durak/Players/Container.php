<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 16:22
 *
 * Контейнер объектов игроков в игре "Дурак"
 */
class Core_Game_Durak_Players_Container extends Core_Game_Players_Container
{

    /**
     * Идентификатор сессии "атакующего" игрока
     *
     * @var string
     */
    protected $_atack;

    /**
     * Идентификатор сессии отбивающегося игрока
     *
     * @var string
     */
    protected $_defender;


    /**
     * Добавление пользователя (убираем установку активности игрока)
     *
     * @param Core_Game_Players_Player $player
     * @param int|null $position Позиция элемента в контейние (игрока за игровым столом)
     * @return Core_Game_Players_Container
     */
    public function addPlayer(Core_Game_Players_Player $player, $position = null)
    {
        //Добавление пользователя в контейнер
        parent::addPlayer($player, $position);
        //Убираем активность игрока
        $player->setActive(false);

        return $this;
    }

    /**
     * Установка "атакующего" игрока
     *
     * @param Core_Game_Durak_Players_Player|string $player Объект игрока либо идентификатор сессии пользователя
     * @return Core_Game_Durak_Players_Container
     * @throws Core_Game_Exception
     */
    public function setAtackPlayer($player)
    {
        //Проверка наличия пользователя
        $index = $this->getIterator()->getElementIndex($player);
        if (false === $index) {
            throw new Core_Game_Exception('Failed to set the defender player. Player does not exists');
        }

        //Получаем идентификатор сессии пользователя
        if ($player instanceof Core_Game_Players_Player) {
            $sid = $player->getSid();
        } elseif (is_scalar($player)) {
            $sid = $player;
        } else {
            throw new Core_Game_Exception('Failed to set the defender player. Invalid type of player SID');
        }

        //Установка идентификатора сессии "атакующего" игрока
        $this->_atack = $sid;

        return $this;
    }

    /**
     * Получение "атакующего" игрока
     *
     * @return Core_Game_Players_Player|bool
     */
    public function getAtackPlayer()
    {
        //Получаем индекс элемента с идентификатором сессии отбивающегося игрока
        $index = $this->getIterator()->getElementIndex($this->_atack);
        //Возвращаем объект игрока
        return $this->getIterator()->getElement($index);
    }

    /**
     * Проверка, является ли игрок "атакующим"
     *
     * @param Core_Game_Durak_Players_Player|string $player
     * @return bool
     */
    public function isAtackPlayer($player)
    {
        if ($player instanceof Core_Game_Players_Player) {
            $sid = $player->getSid();
        } else {
            $sid = $player;
        }

        return $sid == $this->_defender;
    }

    /**
     * Установка отбивающегося игрока
     *
     * @param Core_Game_Durak_Players_Player|string $player Объект игрока либо идентификатор сессии пользователя
     * @return Core_Game_Durak_Players_Container
     * @throws Core_Game_Exception
     */
    public function setDefender($player)
    {
        //Проверка наличия пользователя
        $index = $this->getIterator()->getElementIndex($player);
        if (false === $index) {
            throw new Core_Game_Exception('Failed to set the defender player. Player does not exists');
        }

        //Получаем идентификатор сессии пользователя
        if ($player instanceof Core_Game_Players_Player) {
            $sid = $player->getSid();
        } elseif (is_scalar($player)) {
            $sid = $player;
        } else {
            throw new Core_Game_Exception('Failed to set the defender player. Invalid type of player SID');
        }

        //Установка идентификатора сессии отбивающегося игрока
        $this->_defender = $sid;

        return $this;
    }

    /**
     * Получение объекта отбивающегося игрока
     *
     * @return Core_Game_Durak_Players_Player|bool
     */
    public function getDefenderPlayer()
    {
        //Получаем индекс элемента с идентификатором сессии отбивающегося игрока
        $index = $this->getIterator()->getElementIndex($this->_defender);
        //Возвращаем объект игрока
        return $this->getIterator()->getElement($index);
    }

    /**
     * Метод проверки отбивается ли указанный игрок
     *
     * @param Core_Game_Durak_Players_Player|string $player
     * @return bool
     */
    public function isDefender($player)
    {
        if ($player instanceof Core_Game_Players_Player) {
            $sid = $player->getSid();
        } else {
            $sid = $player;
        }

        return $sid == $this->_defender;
    }

    /**
     * Получение списка пользователей в игре
     *
     * @return Core_Game_Durak_Players_Player[]
     */
    public function getPlayersInGame()
    {
        $result = array();
        foreach($this->getIterator() as $player) {
            if ($player->isPlay()) {
                $result[] = $player;
            }
        }
        return $result;
    }

    /**
     * Получение списка пользователей вышедших из игры (победителей)
     *
     * @return Core_Game_Durak_Players_Player[]
     */
    public function getPlayersOutGame()
    {
        $result = array();
        foreach($this->getIterator() as $player) {
            if (!$player->isPlay()) {
                $result[] = $player;
            }
        }
        return $result;
    }

    /**
     * Получение среднего значения очков игроков
     *
     * @return float
     */
    public function getAvaragePoints()
    {
        $points = 0;
        foreach($this->getIterator() as $element) {
            $points += $element->getPoints();
        }

        return $points / $this->count();
    }

    /**
     * Получение пользователя с максимальным количеством очков
     *
     * @return Core_Game_Durak_Players_Player
     */
    public function getMaxPointsPlayer()
    {
        $max = null;
        $player = null;
        foreach($this->getIterator() as $element) {
            if ($element->getPoints() > $max) {
                $max = $element->getPoints();
                $player = $element;
            }
        }

        return $player;
    }

    /**
     * Переключение текущего активного игрока
     *
     * @param bool $isDefend Флаг, отбился ли игрок в предыдущем розыгрыше
     */
    public function switchActivePlayer($isDefend = true)
    {
        if ($this->count() < 4) {
            //Одиночная игра
            $this->switchInSingleGame($isDefend);
        } else {
            //Парная игра
            $this->switchInDoubleGame($isDefend);
        }
    }

    /**
     * Переключение активного и отбивающегося пользователя в одиночной игре
     *
     * @param bool $isDefend Флаг, отбился ли игрок в предыдущем розыгрыше
     */
    private function switchInSingleGame($isDefend = true)
    {
        //Если игрок отбился и у него есть на рука карты - его ход
        if ($isDefend && count($this->getDefenderPlayer()->getCardArray())) {
            //Установка активного пользователя
            $this->setActive($this->getDefenderPlayer());
            //Установка атакующего игрока
            $this->setAtackPlayer($this->getDefenderPlayer());
        } else {
            //Устанавливаем следующего игрока по списку после отбивавшегося в качестве активного и атакующего
            $this->getIterator()->setCurrentElement($this->getDefenderPlayer());
            $player = $this->_getNextPlayer();
            $this->setActive($player);
            $this->setAtackPlayer($player);
        }

        //Устанавливаем в качестве отбивающегося, следующего пользователя по списку после атакующего
        $this->getIterator()->setCurrentElement($this->getAtackPlayer());
        $this->setDefender($this->_getNextPlayer());
    }

    /**
     * Переключение активного и отбивающегося пользователя в парной игре
     *
     * @param bool $isDefend Флаг, отбился ли игрок в предыдущем розыгрыше
     */
    private function switchInDoubleGame($isDefend = true)
    {
        //Определяем следующего отбивающегося пользователя
        $this->getIterator()->setCurrentElement($this->getDefenderPlayer());
        $defender = $this->getIterator()->nextElement();
        if ($isDefend) {
            //Отбивается следующий соперник
            while($defender->isPartner($this->getDefenderPlayer()) || !$defender->isPlay()) {
                $defender = $this->getIterator()->nextElement();
            }
        } else {
            //Отбивается партнер
            while(!$defender->isPartner($this->getDefenderPlayer()) || !$defender->isPlay()) {
                $defender = $this->getIterator()->nextElement();
            }
        }
        //Установка следующего отбивающегося пользователя
        $this->setDefender($defender);

        //Установка курсора на отбивающемся игроке
        $this->getIterator()->setCurrentElement($defender);
        //Определение активного игрока (перед отбивающимся)
        $active = $this->getIterator()->prevElement();
        while($active->isPartner($defender) || !$active->isPlay()) {
            $active = $this->getIterator()->prevElement();
        }
        //Установка активного пользователя
        $this->setActive($active);
        //Установка атакующего игрока
        $this->setAtackPlayer($active);
    }

    /**
     * Получение следующего, участвующего в игре, пользователя
     *
     * @return Core_Game_Players_Player
     */
    private function _getNextPlayer()
    {
        $currentSid = $this->getIterator()->getCurrentElement()->getSid();
        $player = $this->getIterator()->nextElement();
        while (!$player->isPlay() && $player->getSid() != $currentSid) {
            $player = $this->getIterator()->nextElement();
        }

        return $player;
    }

}
