<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 11:37
 *
 * Описание игральной карты
 */
class Core_Game_Durak_Cards_Card
{

    /**
     * Карточные масти
     */
    const SUIT_HEARTS = 'H'; //Черви
    const SUIT_DIAMONDS = 'D'; //Бубны
    const SUIT_CLUBS = 'C'; //Трефы
    const SUIT_SPADES = 'S'; //Пики

    /**
     * Старшинство карт
     */
    const SIX = 6;
    const SEVEN = 7;
    const EIGHT = 8;
    const NINE = 9;
    const TEN = 10;
    const JACK = 11;
    const QUEEN = 12;
    const KING = 13;
    const ACE = 14;

    /**
     * Масть карты
     *
     * @var string
     */
    protected $_suit;

    /**
     * Старшинство карты
     *
     * @var int
     */
    protected $_value;


    /**
     * Создание объекта карты
     *
     * @param string|null $suit Масть
     * @param int|null $value Значение
     */
    public function __construct($suit = null, $value = null)
    {
        if (null !== $suit) {
            $this->setSuit($suit);
        }
        if (null !== $value) {
            $this->setValue($value);
        }
    }

    /**
     * Создание объекта карты
     *
     * @static
     * @param string $strCard Данные карты в виде строки
     * @return Core_Game_Durak_Cards_Card
     * @throws Core_Game_Durak_Exception
     */
    public static function create($strCard)
    {
        //Парсим данные карты
        $strCard = trim(strtoupper($strCard));
        $suit = substr($strCard, 0, 1);
        $value = intval(substr($strCard, 1));

        //Проверка валидности масти карты
        if ($suit != self::SUIT_HEARTS &&
            $suit != self::SUIT_DIAMONDS &&
            $suit != self::SUIT_CLUBS &&
            $suit != self::SUIT_SPADES) {
            throw new Core_Game_Durak_Exception('Invalid card format ' . $suit, 3003, Core_Exception::USER);
        }

        //Проверяем валидность значения старшинства карты
        if ($value < self::SIX || $value > self::ACE) {
            throw new Core_Game_Durak_Exception('Invalid card format', 3003, Core_Exception::USER);
        }

        //Возвращаем объект карты
        return new self($suit, $value);
    }

    /**
     * Установка масти карты
     *
     * @param string $suit
     * @return Core_Game_Durak_Cards_Card
     */
    public function setSuit($suit)
    {
        $this->_suit = strtoupper($suit);
        return $this;
    }

    /**
     * Получение масти карты
     *
     * @return string
     */
    public function getSuit()
    {
        return $this->_suit;
    }

    /**
     * Установка старшинства карты
     *
     * @param int $value
     * @return Core_Game_Durak_Cards_Card
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * Получение старшинства карты
     *
     * @return int
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Проверка страшинства карты.
     * Проверяется, является ли текущая карта старше указанной
     *
     * @param Core_Game_Durak_Cards_Card $card
     * @param string $trump Козырь
     * @return bool
     * @throws Core_Game_Durak_Exception
     */
    public function isUpper(Core_Game_Durak_Cards_Card $card, $trump)
    {
        if ($this->getSuit() == $card->getSuit()) {
            return $this->getValue() > $card->getValue();
        } elseif ($this->getSuit() == strtoupper($trump)) {
            return true;
        } elseif ($card->getSuit() == strtoupper($trump)) {
            return false;
        } else {
            throw new Core_Game_Durak_Exception('Failed to compare cards of different suits', 3004, Core_Exception::USER);
        }
    }

    /**
     * Проверка страшинства карты.
     * Проверяется, является ли текущая карта младше указанной
     *
     * @param Core_Game_Durak_Cards_Card $card
     * @param string $trump Козырь
     * @return bool
     * @throws Core_Game_Durak_Exception
     */
    public function isLower(Core_Game_Durak_Cards_Card $card, $trump)
    {
        if ($this->getSuit() == $card->getSuit()) {
            return $this->getValue() < $card->getValue();
        } elseif ($this->getSuit() == strtoupper($trump)) {
            return false;
        } elseif ($card->getSuit() == strtoupper($trump)) {
            return true;
        } else {
            throw new Core_Game_Durak_Exception('Failed to compare cards of different suits', 3004, Core_Exception::USER);
        }
    }

    /**
     * Проверка эквивалентности масти указанной карты с текущей
     *
     * @param Core_Game_Durak_Cards_Card|string $card
     * @return bool
     */
    public function equalSuit($card)
    {
        if (!$card instanceof self) {
            $card = self::create($card);
        }

        return $this->getSuit() == $card->getSuit();
    }

    /**
     * Проверка эквивалентности старшинства указанной карты с текущей
     *
     * @param Core_Game_Durak_Cards_Card|string $card
     * @return bool
     */
    public function equalValue($card)
    {
        if (null == $card) {
            return false;
        }
        if (!$card instanceof self) {
            $card = self::create($card);
        }

        return $this->getValue() == $card->getValue();
    }

    /**
     * Получение данных карты в виде строки
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSuit() . $this->getValue();
    }

}
