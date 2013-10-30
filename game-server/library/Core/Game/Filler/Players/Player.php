<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 22.02.12
 * Time: 12:58
 *
 * Описание объекта игрока
 */
class Core_Game_Filler_Players_Player extends Core_Game_Players_Player
{

    /**
     * Текущий цвет полей пользователя на игровой таблице
     *
     * @var int
     */
    protected $_color;


    /**
     * Установка текущего цвета полей пользователя
     *
     * @param int $color
     * @return Core_Game_Filler_Players_Player
     */
    public function setColor($color)
    {
        $this->_color = $color;
        return $this;
    }

    /**
     * Получение текущего цвета полей пользователя
     *
     * @return int
     */
    public function getColor()
    {
        return $this->_color;
    }

    /**
     * Получение данных объекта в виде массива
     *
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();
        $result['color'] = $this->getColor();

        return $result;
    }

}
