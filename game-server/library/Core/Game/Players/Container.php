<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 20.02.12
 * Time: 14:03
 *
 * Контейнер объектов игроков
 */
class Core_Game_Players_Container implements IteratorAggregate, Countable
{

    /**
     * Список пользователей
     *
     * @var Core_Game_Players_Iterator
     */
    protected $_iterator;


    /**
     * Создание нового объекта контейнера пользователей
     *
     * @param int $maxPlayersCount Максимальное количество игроков
     */
    public function __construct($maxPlayersCount = 2)
    {
        $this->_iterator = new Core_Game_Players_Iterator($maxPlayersCount);
    }

    /**
     * (PHP 5 >= 5.1.0)
     *
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Core_Game_Players_Iterator
     */
    public function getIterator()
    {
        return $this->_iterator;
    }

    /**
     * (PHP 5 <= 5.1.0)
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->getIterator());
    }

    /**
     * Добавление пользователя
     *
     * @param Core_Game_Players_Player $player
     * @param int|null $position Позиция элемента в контейние (игрока за игровым столом)
     * @return Core_Game_Players_Container
     */
    public function addPlayer(Core_Game_Players_Player $player, $position = null)
    {
        //Добавление нового элемента в список
        $this->getIterator()->addElement($player, $position);
        //Если это первый игрок, устанавливаем его как активного
        if (count($this->getIterator()) == 1) {
            $this->setActive($player);
        }
        return $this;
    }

    /**
     * Получение объекта пользователя
     *
     * @param string $sid Идентификатор сессии пользователя
     * @param mixed $default Возвращаемое значение в случае отсутствия пользователя
     * @return Core_Game_Players_Player|mixed
     */
    public function getPlayer($sid, $default = null)
    {
        //Поиск индекса пользователя с указанным идентификатором сессии
        $index = $this->getIterator()->getElementIndex($sid);
        //Проверка наличия игрока
        if (false !== $index) {
            //Возвращаем объект пользователя
            return $this->getIterator()->getElement($index);
        } else {
            //Пользователь не найден
            return $default;
        }
    }

    /**
     * Проверка наличия игрока
     *
     * @param string $sid Идентификатор сессии пользовталя
     * @return bool
     */
    public function hasPlayer($sid)
    {
        //Поиск пользователя с указанным идентификатором сессии
        return false !== $this->getIterator()->getElementIndex($sid);
    }

    /**
     * Удаление пользователя из контейнера
     *
     * @param Core_Game_Players_Player|string $player
     * @return bool
     */
    public function deletePlayer($player)
    {
        return $this->getIterator()->unsetElement($player);
    }

    /**
     * Получение позиции игрока
     *
     * @param Core_Game_Players_Player|string $player
     *
     * @return bool|int
     */
    public function getPlayerPosition($player)
    {
        //Получение индекса элемента в итераторе
        return $this->getIterator()->getElementIndex($player);
    }

    /**
     * Установка активного игрока
     *
     * @param Core_Game_Players_Player|string $player
     * @param bool $single Флаг - только один игрок может быть активным
     * @return Core_Game_Players_Container
     * @throws Core_Game_Exception
     */
    public function setActive($player, $single = true)
    {
        //Установка активности игроков
        foreach($this->getIterator() as $element) {
            if ($element->getSid() == $player) {
                $element->setActive();
            } elseif ($single == true) {
                $element->setActive(false);
            }
        }

        return $this;
    }

    /**
     * Получение объекта активного игрока
     *
     * @return Core_Game_Players_Player|mixed
     */
    public function getActivePlayer()
    {
        //Получаем копию итератора (чтобы не сбить последовательность, если метод вызван в цикле)
        $iterator = clone($this->getIterator());
        //Поиск активного игрока
        $sid = false;
        foreach($iterator as $element) {
            if ($element->isActive()) {
                $sid = $element->getSid();
            }
        }

        //Проверка наличия активного игрока
        if (false !== $sid) {
            return $this->getPlayer($sid);
        }
        return false;
    }

    /**
     * ДЛЯ ПОДДЕРЖКИ СТАРОЙ РЕАЛИЗАЦИИ (в классе игрока не было поля active)
     * Проверка активности игрока
     *
     * @param Core_Game_Players_Player|string $player
     * @return bool
     */
    public function isActive($player)
    {
        if (is_scalar($player)) {
            $player = $this->getPlayer($player);
        }

        return $player->isActive();
    }

    /**
     * Поиск объекта пользователя по данным указанного поля
     *
     * @param string $field Поле объекта пользователя по которому ведется поиск
     * @param mixed $value Искомое значение поля
     * @param bool $default
     * @return Core_Game_Players_Player|bool
     */
    public function find($field, $value, $default = false)
    {
        //Для поддержки работы внутри цикла клонирум итератор чтобы не сбить курсор
        $iterator = clone($this->getIterator());
        foreach($iterator as $player) {
            if ($player->$field == $value) {
                return $player;
            }
        }

        return $default;
    }

    /**
     * Переключение текущего активного игрока
     */
    public function switchActivePlayer()
    {
        //Установка курсора списка на активном игроке
        $this->getIterator()->setCurrentElement($this->getActivePlayer());
        //Переход маркета активности к следующему игроку
        $this->setActive($this->getIterator()->nextElement());
    }
}
