<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 21.02.12
 * Time: 15:12
 *
 * Итератор списка игроков
 */
class Core_Game_Players_Iterator implements Iterator, Countable
{
    /**
     * Данные итератора
     *
     * @var array
     */
    private $d = array();

    /**
     * Текущая позиция курсора в итераторе
     *
     * @var int
     */
    private $p = 0;

    /**
     * Допустимое количество элементов
     *
     * @var int
     */
    private $c = 0;


    /**
     * Создание нового объекта итератора
     *
     * @param int $count Допустимое количество игроков
     */
    public function __construct($count)
    {
        $this->c = $count;
    }

    /**
     * Установка допустимого количества элементов
     *
     * @param int $count
     */
    public function setElementsCount($count)
    {
        $this->c = $count;
    }

    /**
     * Получение допустимого количества элементов
     *
     * @return int
     */
    public function getElementsCount()
    {
        return $this->c;
    }

    /**
     * Добавление нового элемента
     *
     * @param Core_Game_Players_Player $value
     * @param int|null                 $index
     * @throws Core_Game_Exception
     */
    public function addElement(Core_Game_Players_Player $value, $index = null)
    {
        //Определение ключа нового элемента
        if (null === $index) {
            //Определение ключа с пустым значением
            for($i = 0; $i < $this->c; $i++) {
                if (!isset($this->d[$i])) {
                    $index = $i;
                    break;
                }
            }
            //Проверка результата поиска ключа
            if (null === $index) {
                throw new Core_Game_Exception('Failed to add new player. Container is full');
            }
        } else {
            //Проверка вхождение индекса в границы доступного количества игроков
            if ($index < 1 || $index > $this->c) {
                throw new Core_Game_Exception('Out of bounds the number of users');
            }
            //Ключи элементов начинаются с нуля
            $index--;
        }

        //Добавление нового элемента
        $this->d[$index] = $value;
        //Сортировка по ключам
        ksort($this->d);
    }

    /**
     * Удаление элемента по его значению
     *
     * @param Core_Game_Players_Player|string $value
     * @return bool
     */
    public function unsetElement($value)
    {
        //Если удалять неободимо текущий элемент переводим курсор на следующую запись
        if ($this->getCurrentElement() == $value) {
            $this->next();
        }

        //Поиск ключа элемента
        $key = array_search($value, $this->d);
        if ($key !== false) {
            unset($this->d[$key]);
            return true;
        }

        return false;
    }

    /**
     * Получение текущего элемента
     *
     * @return Core_Game_Players_Player
     */
    public function getCurrentElement()
    {
        if ($this->valid()) {
            return $this->current();
        } else {
            return $this->nextElement();
        }
    }

    /**
     * Установка текущей позиции курсора на элементе с указанным индексом
     *
     * @param int $index
     * @return bool
     */
    public function setCurrentIndex($index)
    {
        $key = $index - 1;
        if (isset($this->d[$key])) {
            $this->p = $key;
            return true;
        }

        return false;
    }

    /**
     * Установка текущей позиции курсора на элементе с указанным значением
     *
     * @param Core_Game_Players_Player|string $value
     * @return bool
     */
    public function setCurrentElement($value)
    {
        //Поиск ключа указанного элемента
        $key = array_search($value, $this->d);
        if (false !== $key) {
            $this->p = $key;
            return true;
        }

        return false;
    }

    /**
     * Получение индекса элемента
     *
     * @param Core_Game_Players_Player|string $value
     * @return int|bool
     */
    public function getElementIndex($value)
    {
        $key = array_search($value, $this->d);
        if (false !== $key) {
            return $key + 1;
        }

        return false;
    }

    /**
     * Получение элемента по его индексу
     *
     * @param int $index
     * @return Core_Game_Players_Player|bool
     */
    public function getElement($index)
    {
        $key = $index - 1;
        if (isset($this->d[$key])) {
            return $this->d[$key];
        }

        return false;
    }

    /**
     * (PHP 5 <= 5.1.0)
     *
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return Core_Game_Players_Player Can return any type.
     */
    public function current()
    {
        return $this->d[$this->p];
    }

    /**
     * (PHP 5 >= 5.1.0)
     *
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        //Идем по ключам до нахождения не пустого элемента
        while(!isset($this->d[++$this->p]) && $this->p < $this->c);
    }

    /**
     * Move revert to next element
     *
     * @return void Any returned value is ignored.
     */
    public function prev()
    {
        //Идем по ключам до нахождения не пустого элемента
        while(!isset($this->d[--$this->p]) && $this->p >= 0);
    }

    /**
     * Получение следующего элемента в списке.
     * Поиск ведется как справа от текущего элемента, так и с начала списка до текущего элемента
     *
     * @return Core_Game_Players_Player
     * @throws Core_Game_Exception
     */
    public function nextElement()
    {
        //Проверка количества элементов
        /*if (count($this->d) <= 1) {
            throw new Core_Game_Exception('Faild to get next element from iterator. The count of elements less than two');
        }*/
        //Переход к следующему элементу
        $this->next();
        //Проверка наличия следующего элемента, справа от текущего
        if (!$this->valid()) {
            //Начинаем поиск следующего элемента с начала списка
            $this->p = -1;
            $this->next();
        }

        //Возвращаем полученный элемент
        return $this->current();
    }

    /**
     * Получение предыдущего элемента в списке
     * Поиск ведется как слева от текущего элемента, так и с конца списка до текущего элемента
     *
     * @return Core_Game_Players_Player
     * @throws Core_Game_Exception
     */
    public function prevElement()
    {
        //Проверка количества элементов
        if (count($this->d) <= 1) {
            throw new Core_Game_Exception('Faild to get previos element from iterator. The count of elements less than two');
        }
        //Переход к предыдущему элементу
        $this->prev();
        //Проверка наличия предыдущего элемента, слева от текущего
        if (!isset($this->d[$this->p])) {
            //Начинаем поиск предыдущего элемента с конца списка
            $this->p = $this->c;
            $this->prev();
        }

        //Возвращаем полученный элемент
        return $this->current();
    }

    /**
     * (PHP 5 >= 5.1.0)
     *
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int scalar on success, integer
     * 0 on failure.
     */
    public function key()
    {
        return $this->p;
    }

    /**
     * (PHP 5 >= 5.1.0)
     *
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if (!count($this->d)) {
            return false;
        }
        if (!isset($this->d[$this->p])) {
            $this->next();
        }
        return $this->p < $this->c;
    }

    /**
     * (PHP 5 >= 5.1.0)
     *
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->p = 0;
    }

    /**
     * (PHP 5 >= 5.1.0)
     *
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->d);
    }
}