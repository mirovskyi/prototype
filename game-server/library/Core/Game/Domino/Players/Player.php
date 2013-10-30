<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 11.06.12
 * Time: 9:44
 * To change this template use File | Settings | File Templates.
 */
class Core_Game_Domino_Players_Player extends Core_Game_Players_Player
{

    /**
     * Массив костей игрока
     *
     * @var Core_Game_Domino_Bone_Array
     */
    protected $_bones;

    /**
     * "Запомненные" очки
     *
     * @var int
     */
    protected $_rememberPoints;


    /**
     * Установка массива костей игрока
     *
     * @param Core_Game_Domino_Bone_Array $bones
     */
    public function setBoneArray(Core_Game_Domino_Bone_Array $bones)
    {
        $this->_bones = $bones;
    }

    /**
     * Получение массива костей игрока
     *
     * @return Core_Game_Domino_Bone_Array|Core_Game_Domino_Bone[]
     */
    public function getBoneArray()
    {
        return $this->_bones;
    }

    /**
     * Установка "запомненных" очков
     *
     * @param int $points
     */
    public function setRememberPoints($points)
    {
        $this->_rememberPoints = $points;
    }

    /**
     * Добавление "запомненных" очков
     *
     * @param int $points
     */
    public function addRememberPoints($points)
    {
        $this->_rememberPoints += $points;
    }

    /**
     * Получение количества запомненных очков
     *
     * @return int
     */
    public function getRememberPoints()
    {
        return $this->_rememberPoints;
    }

    /**
     * Обнуление "запомненных" очков
     */
    public function resetRememberPoints()
    {
        $this->_rememberPoints = 0;
    }

    /**
     * Добавление очков пользователя
     *
     * @param int $points
     */
    public function addPoints($points)
    {
        //Если счет открыт, добавляем очки к счету игрока
        if ($this->getPoints() > 0) {
            $this->_points += $points;
            return;
        }

        //Счет еще не был открыт, проверка необходимости открытия счета
        if ($points > 12) {
            //Добавляем запомненные очки
            $points += $this->getRememberPoints();
            //Обнуляем запомненные очки
            $this->resetRememberPoints();
            //Создание счета
            $this->setPoints($points);
            return;
        }

        //"Запоминаем" очки
        $this->addRememberPoints($points);
    }

}
