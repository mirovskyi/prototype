<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 09.06.12
 * Time: 14:41
 *
 * Класс описывающий игральную кость домино
 */
class Core_Game_Domino_Bone
{

    /**
     * Максимальное значение на одной стороне кости
     */
    const MAX_VALUE = 6;

    /**
     * Значение левой стороны игральной кости
     *
     * @var int
     */
    protected $_lv;

    /**
     * Значение правой стороны игральной кости
     *
     * @var int
     */
    protected $_rv;


    /**
     * Создание нового объекта игральной кости
     *
     * @param string|null $value
     */
    public function __construct($value = null)
    {
        if (null !== $value) {
            $this->setValue($value);
        }
    }

    /**
     * Установка значения игральной кости
     *
     * @param string $value Формат 'leftValue:rightValue'
     *
     * @throws Core_Game_Domino_Exception
     */
    public function setValue($value)
    {
        if (null == $value || !preg_match('/^\d:\d$/', $value)) {
            throw new Core_Game_Domino_Exception();
        }

        $values = explode(':', $value);
        $this->setLeftValue($values[0]);
        $this->setRightValue($values[1]);
    }

    /**
     * Получение значения игральной кости в формате 'leftValue:rightValue'
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_lv . ':' . $this->_rv;
    }

    /**
     * Установка значение левой части игральной кости
     *
     * @param int $value
     *
     * @throws Core_Game_Domino_Exception
     */
    public function setLeftValue($value)
    {
        if ($value < 0 || $value > self::MAX_VALUE) {
            throw new Core_Game_Domino_Exception();
        }

        $this->_lv = $value;
    }

    /**
     * Установка значения правой части игральной кости
     *
     * @param int $value
     *
     * @throws Core_Game_Domino_Exception
     */
    public function setRightValue($value)
    {
        if ($value < 0 || $value > self::MAX_VALUE) {
            throw new Core_Game_Domino_Exception();
        }

        $this->_rv = $value;
    }

    /**
     * Получение значения левой части игральной кости
     *
     * @return int
     */
    public function getLeftValue()
    {
        return $this->_lv;
    }

    /**
     * Получение значения правой части игральной кости
     *
     * @return int
     */
    public function getRightValue()
    {
        return $this->_rv;
    }

    /**
     * Проверка парного значения игральной кости
     *
     * @return bool
     */
    public function isDouble()
    {
        if ($this->getLeftValue() === null) {
            return false;
        }
        return $this->getLeftValue() === $this->getRightValue();
    }

    /**
     * Проверка наличия значения на одной из сторон игральной кости
     *
     * @param int $value
     *
     * @return bool
     */
    public function hasValue($value)
    {
        if ($this->_lv == $value || $this->_rv == $value) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получение суммы значений на кости
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->getLeftValue() + $this->getRightValue();
    }

    /**
     * Повернуть игральную кость на 180 градусов
     */
    public function turn()
    {
        $this->_lv += $this->_rv;
        $this->_rv = $this->_lv - $this->_rv;
        $this->_lv = $this->_lv - $this->_rv;
    }

    /**
     * Magic method __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }

}
