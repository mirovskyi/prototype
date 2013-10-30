<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 21.06.12
 * Time: 9:49
 *
 * Класс описывающий игральные кости (зары)
 */
class Core_Game_Backgammon_Dice
{

    /**
     * Список значений игральных костей
     *
     * @var array
     */
    protected $_values = array();


    /**
     * Установка значений игральных костей
     *
     * @throws Core_Game_Backgammon_Exception
     */
    public function setValues()
    {
        //Проверка количества игральных костей
        if (!func_num_args() || func_num_args() % 2 != 0 || func_num_args() > 4) {
            throw new Core_Game_Backgammon_Exception('Invalid dice values count');
        }

        if (func_num_args()) {
            //Получение списка значений
            $values = func_get_args();
            //Проверка "дубля", при этом количество ходов увеличивается в двое (4 хода)
            if (array_sum($values) / count($values) == $values[0]) {
                $values = array_fill(0, 4, $values[0]);
            }
            //Добавление значений игральных костей
            foreach($values as $val) {
                $this->addValue($val);
            }
        }
    }

    /**
     * Получение списка игральных костей
     *
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     * Добавление значения игральной кости
     *
     * @param int $value Значение игральной кости
     *
     * @throws Core_Game_Backgammon_Exception
     */
    public function addValue($value)
    {
        if ($value < 0 || $value > 6) {
            throw new Core_Game_Backgammon_Exception('Invalid die value');
        }
        array_push($this->_values, array('v' => $value, 'm' => 0));
    }

    /**
     * Проверка наличия значения игральных костей
     *
     * @param int  $value
     * @param bool $free
     *
     * @return bool
     */
    public function hasValue($value, $free = true)
    {
        $sum = 0;
        foreach($this->_values as $valueInfo) {
            if ($free && $valueInfo['m'] > 0) {
                continue;
            }
            if ($valueInfo['v'] == $value) {
                return true;
            }
            $sum += $valueInfo['v'];
            if ($sum == $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Использование значения игральных костей
     *
     * @param int $value
     *
     * @return bool
     */
    public function useValue($value)
    {
        $sum = 0;
        $sumIndex = array();
        foreach($this->_values as $index => $valueInfo) {
            if ($valueInfo['m'] > 0) {
                continue;
            }
            if ($valueInfo['v'] == $value) {
                $this->_values[$index]['m'] = 1;
                return true;
            }
            $sum += $valueInfo['v'];
            $sumIndex[] = $index;
            if ($sum == $value) {
                foreach($sumIndex as $i) {
                    $this->_values[$i]['m'] = 1;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Проверка наличия использованного значения выпавших игральных костей
     *
     * @return bool
     */
    public function hasUseValues()
    {
        foreach($this->_values as $valueInfo) {
            if ($valueInfo['m'] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Получение всех возможных свободных значений
     *
     * @return array
     */
    public function getFreeValues()
    {
        $possibleValues = array();
        foreach($this->getValues() as $value) {
            if ($value['m'] > 0)  {
                continue;
            }
            $possibleValues[] = $value['v'];
        }

        return $possibleValues;
    }

    /**
     * Получение списока возможных сумм значений (свободных) на игральных костях
     *
     * @return array
     */
    public function getFreeSumValues()
    {
        //Массив сумм свободных значений
        $arrSum = array();
        //Получение массива не использованных значений игровых костей
        $freeValues = $this->getFreeValues();
        //Суммы быть не может если значений менее двух
        if (count($freeValues) > 1) {
            //В качестве начального значения суммы используем первое значение
            $sum = array_shift($freeValues);
            //Проходим по оставшимся значеням
            foreach($freeValues as $value) {
                //Добавляем знаение кости к сумме
                $sum += $value;
                //Добавляем значение суммы в список возможных сумм значений на игральных костях
                $arrSum[] = $sum;
            }
        }

        //Возвращаем список возможных сумм значений на игральных костях
        return $arrSum;
    }

    /**
     * Проверка наличия не использованных значений игральных костей
     *
     * @return bool
     */
    public function hasFreeValues()
    {
        foreach($this->_values as $valueInfo) {
            if (!$valueInfo['m']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Проверка дубля (идинаковых значений игральных костей)
     *
     * @return bool
     */
    public function isDouble()
    {
        return count($this->_values) > 2;
    }

    /**
     * Очистка данных игровых костей
     *
     * @return void
     */
    public function clear()
    {
        $this->_values = array();
    }

    /**
     * Значение игральных костей в виде строки
     *
     * @return string
     */
    public function __toString()
    {
        if (count($this->_values) >= 2) {
            return $this->_values[0]['v']  . ':' . $this->_values[1]['v'];
        } else {
            return '';
        }
    }

}
