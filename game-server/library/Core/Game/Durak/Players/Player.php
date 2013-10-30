<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 11:35
 *
 * Описание игрока
 */
class Core_Game_Durak_Players_Player extends Core_Game_Players_Player
{

    /**
     * Массив карт игрока
     *
     * @var Core_Game_Durak_Cards_Array
     */
    protected $_cards;

    /**
     * Установка массива карт игрока
     *
     * @param Core_Game_Durak_Cards_Array $cards
     * @return Core_Game_Durak_Players_Player
     */
    public function setCardArray(Core_Game_Durak_Cards_Array $cards)
    {
        $this->_cards = $cards;
        return $this;
    }

    /**
     * Получение массива карт игрока
     *
     * @return Core_Game_Durak_Cards_Array
     */
    public function getCardArray()
    {
        return $this->_cards;
    }

    /**
     * Проверка наличия карт у игрока
     *
     * @return bool
     */
    public function hasCards()
    {
        return (bool)count($this->_cards);
    }

    /**
     * Проверка, является ли указанный игрок партнером текущего
     *
     * @param Core_Game_Durak_Players_Player $player
     * @return bool
     */
    public function isPartner(Core_Game_Durak_Players_Player $player)
    {
        //Проверяем, не является ли переданный игрок токущим объектом
        if ($this == $player) {
            return false;
        }
        //Разница идентификаторов пользователей (позиций за игровым столом)
        $diff = abs($this->getId() - $player->getId());
        //Если разница делится на 2 без остатка - игроки находятся друг напротив друга (партнеры)
        return $diff % 2 == 0;
    }
}
