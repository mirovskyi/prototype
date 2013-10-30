<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 11.06.12
 * Time: 9:50
 *
 * Класс описывающий список (ряд) игральных костей Домино
 */
class Core_Game_Domino_Bone_Array implements IteratorAggregate, Countable
{

    /**
     * Способы добавления игральной кости в массив
     */
    const PREPEND = 0;
    const APPEND = 1;

    /**
     * Массив игральных костей
     *
     * @var Core_Game_Domino_Bone[]
     */
    protected $_bones = array();


    /**
     * Создание нового объекта массива игральных костей
     *
     * @param Core_Game_Domino_Bone[]|null $bones
     */
    public function __construct(array $bones = null)
    {
        if (null !== $bones) {
            $this->setBones($bones);
        }
    }

    /**
     * Magic method __sleep
     *
     * @return array
     */
    public function __sleep()
    {
        $this->_bones = $this->__toString();
        return array('_bones');
    }

    /**
     * Magic method __wakeup
     *
     * @return void
     */
    public function __wakeup()
    {
        //Обработка пустой строки
        if (null == $this->_bones) {
            $this->_bones = array();
        }
        //Обработка строкового значения в данных списка игральных костей
        if (is_string($this->_bones)) {
            $strBones = $this->_bones;
            $this->_bones = array();
            foreach(explode(',', $strBones) as $bone) {
                $this->addBone($bone);
            }
        }
    }

    /**
     * Установка массива игральных костей
     *
     * @param Core_Game_Domino_Bone[] $bones
     */
    public function setBones(array $bones)
    {
        $this->_bones = $bones;
    }

    /**
     * Получение списка игральных костей
     *
     * @return Core_Game_Domino_Bone[]
     */
    public function getBones()
    {
        $this->__wakeup();
        return $this->_bones;
    }

    /**
     * Добавление игральной кости в массив
     *
     * @param Core_Game_Domino_Bone|string $bone Объект игральной кости
     * @param int                          $placement Способ добавления кости в массив, в начало или в конец
     */
    public function addBone($bone, $placement = self::APPEND)
    {
        if (is_string($bone)) {
            $bone = new Core_Game_Domino_Bone($bone);
        }

        //Если список пуст, добавляем первый элемент в массив
        if (!$this->count()) {
            $this->_bones[] = $bone;
            return;
        }

        //Добавление элемента в начало или конец ряда
        if ($placement == self::PREPEND) {
            array_unshift($this->_bones, $bone);
        } else {
            array_push($this->_bones, $bone);
        }
    }

    /**
     * Удаление игральной кости из массива
     *
     * @param Core_Game_Domino_Bone|string $bone
     */
    public function delBone($bone)
    {
        if (is_string($bone)) {
            $bone = new Core_Game_Domino_Bone($bone);
        }
        //Поиск индекса игральной кости в массиве
        $index = $this->_search($bone);
        //Если элемент существует, удаляем его
        if (false !== $index) {
            unset($this->_bones[$index]);
        }
    }

    /**
     * Проверка наличия игральной кости в массиве
     *
     * @param Core_Game_Domino_Bone|string $bone
     *
     * @return bool
     */
    public function hasBone($bone)
    {
        if (is_string($bone)) {
            $bone = new Core_Game_Domino_Bone($bone);
        }
        //Поиск индекса игральной кости в массиве
        $index = $this->_search($bone);
        if (false !== $index) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверка наличия значения стороны кости в массиве иральных костей
     *
     * @param int|array $value
     *
     * @return bool
     */
    public function hasValue($value)
    {
        $value = (array)$value;
        foreach($this->getBones() as $bone) {
            foreach($value as $val) {
                if ($bone->hasValue($val)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Получение знаения левой стороны ряда костей
     *
     * @return bool|int
     */
    public function getLeftValue()
    {
        //Проверка наличия костей в массиве
        if (!$this->count()) {
            return false;
        }
        //Получаем первую кость в массиве
        $bone = $this->_bones[0];
        //Отдаем значение левой стороны кости
        return $bone->getLeftValue();
    }

    /**
     * Получение значения правой стороны ряда костей
     *
     * @return bool|int
     */
    public function getRightValue()
    {
        //Проверка наличия костей в массиве
        if (!$this->count()) {
            return false;
        }
        //Получаем последнюю кость в массиве
        $bone = $this->_bones[$this->count() - 1];
        //Отдаем значение правой стороны кости
        return $bone->getRightValue();
    }

    /**
     * Получение общей суммы значений игральных костей
     *
     * @return int
     */
    public function getAmount()
    {
        $amount = 0;
        foreach($this->getBones() as $bone) {
            //Получаем сумму значений на кости
            $boneAmount = $bone->getAmount();
            //Дубль 0-0 считается как 10 очков
            if ($boneAmount == 0) {
                $boneAmount = 10;
            }
            //Добавление суммы кости к общей сумме
            $amount += $boneAmount;
        }
        return $amount;
    }

    /**
     * Перемешивание массива игральных костей
     *
     * @return void
     */
    public function shuffle()
    {
        if ($this->count()) {
            shuffle($this->_bones);
        }
    }

    /**
     * Извлечение игральной кости из конца массива
     *
     * @return Core_Game_Domino_Bone
     */
    public function pop()
    {
        if (!$this->count()) {
            return null;
        }
        return array_pop($this->_bones);
    }

    /**
     * Извлечение игральной кости из начала массива
     *
     * @return Core_Game_Domino_Bone
     */
    public function shift()
    {
        if (!$this->count()) {
            return null;
        }
        return array_shift($this->_bones);
    }

    /**
     * Оистка массива игральных костей
     *
     * @return void
     */
    public function clear()
    {
        $this->_bones = array();
    }

    /**
     * Получение списка скрытых костей в виде строки
     *
     * @return string
     */
    public function showHiddenBones()
    {
        if (!$this->count()) {
            return '';
        }
        $arrBones = array_fill(0, $this->count(), 'X');
        return implode(',', $arrBones);
    }

    /**
     * Magic method __toString
     *
     * @return string
     */
    public function __toString()
    {
        return implode(',', $this->getBones());
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing Iterator or
     * Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getBones());
    }

    /**
     * (PHP 5 >= 5.1.0)
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->getBones());
    }

    /**
     * Поиск игральной кости в массиве.
     * Возвращает индекс найденного элемента в массиве либо FALSE
     *
     * @param Core_Game_Domino_Bone $searchBone
     *
     * @return bool|int
     */
    private function _search(Core_Game_Domino_Bone $searchBone)
    {
        foreach($this->getBones() as $index => $bone) {
            if ($searchBone == $bone) {
                return $index;
            }
            $bone->turn();
            if ($searchBone == $bone) {
                $bone->turn();
                return $index;
            }
            $bone->turn();
        }
        return false;
    }
}
