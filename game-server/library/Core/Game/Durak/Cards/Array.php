<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 12:12
 *
 * Массив игровых карт
 */
class Core_Game_Durak_Cards_Array implements IteratorAggregate, Countable
{

    /**
     * Массив объектов карт
     *
     * @var Core_Game_Durak_Cards_Card[]
     */
    protected $_cards = array();


    /**
     * Создание массива карт
     *
     * @param array|null $cards
     */
    public function __construct(array $cards = null)
    {
        if (null !== $cards) {
            $this->setCards($cards);
        }
    }

    /**
     * Magic method __sleep
     *
     * @return array
     */
    public function __sleep()
    {
        //Преобразование массива карт в строку
        $this->_cards = $this->__toString();
        return array('_cards');
    }

    /**
     * Magic method __wakeup
     *
     * @return void
     */
    public function __wakeup()
    {
        //Преобразование строки в массив карт
        $strCards = $this->_cards;
        $this->_cards = array();
        if ($strCards) {
            foreach(explode(',', $strCards) as $card) {
                $this->add($card);
            }
        }
    }

    /**
     * Установка массива карт
     *
     * @param Core_Game_Durak_Cards_Card[] $cards
     */
    public function setCards(array $cards)
    {
        $this->_cards = $cards;
    }

    /**
     * Получение массива карт
     *
     * @return Core_Game_Durak_Cards_Card[]
     */
    public function getCards()
    {
        //!!!Если объект используется после сериализации (сохранение данных игры в хранилище), данные карт изменены на строку
        if (is_string($this->_cards)) {
            //Возвращаем данные карт в исходный тип
            $this->__wakeup();
        }
        return $this->_cards;
    }

    /**
     * Добавление карты в массив
     *
     * @param Core_Game_Durak_Cards_Array|Core_Game_Durak_Cards_Card[]|Core_Game_Durak_Cards_Card|string $card
     * @throws Core_Game_Durak_Exception
     * @return Core_Game_Durak_Cards_Array
     */
    public function add($card)
    {
        if (is_string($card)) {
            $card = Core_Game_Durak_Cards_Card::create($card);
        }
        if (!$card instanceof Core_Game_Durak_Cards_Array && !is_array($card)) {
            $card = array($card);
        }
        //Добавление объектов карт
        foreach($card as $cardObject) {
            if (!$cardObject instanceof Core_Game_Durak_Cards_Card) {
                throw new Core_Game_Durak_Exception('Added not card instance in CardArray');
            }
            $this->_cards[] = $cardObject;
        }

        return $this;
    }

    /**
     * Удаление карты из массива
     *
     * @param Core_Game_Durak_Cards_Card|string $card
     */
    public function delete($card)
    {
        $index = array_search($card, $this->getCards());
        if (false !== $index) {
            unset($this->_cards[$index]);
        }
    }

    /**
     * Очистка массива карт
     *
     * @return void
     */
    public function clear()
    {
        $this->_cards = array();
    }

    /**
     * Извлечение карты из конца массива
     *
     * @return Core_Game_Durak_Cards_Card[]
     */
    public function pop()
    {
        $card = array_pop($this->_cards);

        return $card;
    }

    /**
     * Извлечение карты из начала массива
     *
     * @return Core_Game_Durak_Cards_Card[]
     */
    public function shift()
    {
        $card = array_shift($this->_cards);

        return $card;
    }

    /**
     * Перемешивание карт
     *
     * @return void
     */
    public function shuffle()
    {
        shuffle($this->_cards);
    }

    /**
     * Проверка перебора карт с одинаковой мастью
     *
     * @return bool
     */
    public function isTooMatchOfSameSuit()
    {
        $suits = array();
        foreach($this->getCards() as $card) {
            $suits[] = $card->getSuit();
        }
        $suits = array_unique($suits);

        return count($suits) <= 2;
    }

    /**
     * Проверка наличия карты
     *
     * @param Core_Game_Durak_Cards_Card|string $card
     *
     * @return bool
     */
    public function hasCard($card)
    {
        return in_array($card, $this->getCards());
    }

    /**
     * Проверка наличия карты с указанным сташинством (значением)
     *
     * @param int $value Старшинство карты
     * @return bool
     */
    public function hasCardValue($value)
    {
        foreach($this->getCards() as $card) {
            if ($card->getValue() == $value) {
                return true;
            }
        }
    }

    /**
     * Получение карты с наименьшим старшинством
     *
     * @param string|null $suit Поиск по определенной масти
     * @return bool|Core_Game_Durak_Cards_Card|null
     */
    public function getMinValue($suit = null)
    {
        if (!count($this->getCards())) {
            return false;
        }
        $min = Core_Game_Durak_Cards_Card::ACE + 1;
        $minCard = false;
        foreach($this->getCards() as $card) {
            if (null !== $suit && $card->getSuit() != $suit) {
                continue;
            }
            if ($card->getValue() < $min) {
                $min = $card->getValue();
                $minCard = $card;
            }
        }

        return $minCard;
    }

    /**
     * Получение списка закрытых карт в виде строки
     *
     * @return string
     */
    public function showHiddenCards()
    {
        //Формирование списка закрытых карт
        $separator = '';
        $cards = '';
        foreach($this->getCards() as $card) {
            $cards .= $separator . 'X';
            $separator = ',';
        }

        return $cards;
    }

    /**
     * (PHP 5 > 5.1.0)
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing Iterator or
     * Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getCards());
    }

    /**
     * (PHP 5 > 5.1.0)
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->getCards());
    }

    /**
     * Получение данных массива карт в виде строки
     *
     * @return string
     */
    public function __toString()
    {
        if (!count($this->getCards())) {
            return '';
        }
        return implode(',', $this->getCards());
    }
}
