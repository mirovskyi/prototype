<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 15:31
 *
 * Колода карт
 */
class Core_Game_Durak_Cards_Pack extends Core_Game_Durak_Cards_Array
{

    /**
     * Количество карт в колоде
     */
    const CARDS_COUNT = 36;

    /**
     * Козырь (масть козыря)
     *
     * @var string
     */
    protected $_trump;


    /**
     * Содание новой колоды карт
     */
    public function __construct()
    {
        //Список мастей
        $suits = array(
            Core_Game_Durak_Cards_Card::SUIT_HEARTS,
            Core_Game_Durak_Cards_Card::SUIT_DIAMONDS,
            Core_Game_Durak_Cards_Card::SUIT_CLUBS,
            Core_Game_Durak_Cards_Card::SUIT_SPADES
        );

        //Добавление в колоду карт каждой масти
        foreach($suits as $suit) {
            $this->add(new Core_Game_Durak_Cards_Card($suit, Core_Game_Durak_Cards_Card::SIX))
                 ->add(new Core_Game_Durak_Cards_Card($suit, Core_Game_Durak_Cards_Card::SEVEN))
                 ->add(new Core_Game_Durak_Cards_Card($suit, Core_Game_Durak_Cards_Card::EIGHT))
                 ->add(new Core_Game_Durak_Cards_Card($suit, Core_Game_Durak_Cards_Card::NINE))
                 ->add(new Core_Game_Durak_Cards_Card($suit, Core_Game_Durak_Cards_Card::TEN))
                 ->add(new Core_Game_Durak_Cards_Card($suit, Core_Game_Durak_Cards_Card::JACK))
                 ->add(new Core_Game_Durak_Cards_Card($suit, Core_Game_Durak_Cards_Card::QUEEN))
                 ->add(new Core_Game_Durak_Cards_Card($suit, Core_Game_Durak_Cards_Card::KING))
                 ->add(new Core_Game_Durak_Cards_Card($suit, Core_Game_Durak_Cards_Card::ACE));
        }

        //Перемешиваем карты в колоде (вместе с первой картой)
        parent::shuffle();

        //Получение и установка масти козыря
        $trumpCard = $this->_cards[0];
        $this->setTrump($trumpCard->getSuit());
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
        return array('_cards', '_trump');
    }

    /**
     * Перемешивание карт в колоде
     *
     * @return void
     */
    public function shuffle()
    {
        //Достаем первую карту из колоды (козырь)
        $trumpCard = array_shift($this->_cards);
        //Перемешиваем карты в колоде
        parent::shuffle();
        //Добавляем козырь опять в начало колоды
        array_unshift($this->_cards, $trumpCard);
    }

    /**
     * Устанока масти козырной карты
     *
     * @param string $suit
     * @return Core_Game_Durak_Cards_Pack
     */
    public function setTrump($suit)
    {
        $this->_trump = $suit;
        return $this;
    }

    /**
     * Получение масти козыря
     *
     * @return string
     */
    public function getTrump()
    {
        return $this->_trump;
    }

    /**
     * Получение козырной карты
     *
     * @return Core_Game_Durak_Cards_Card|bool
     */
    public function getTrumpCard()
    {
        $cards = $this->getCards();
        if (isset($cards[0])) {
            return $cards[0];
        }

        return false;
    }

}
