<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 26.04.12
 * Time: 17:20
 *
 * Класс работы с товарами магазина
 */
class Core_Shop_Items
{

    /**
     * Идентификаторы (наименование) товаров магазина
     */
    const VIP = 'vip';
    const CHESS_BOARD = 'chess_board';
    const CHESS_CLOCK = 'chess_clock';
    const SAND_CLOCK = 'sand_clock';
    const GLASSES = 'glasses';
    const POOL = 'pool';
    const FILLER_BOARD = 'filler';
    const BACKGAMMON_BOARD = 'nardi';
    const MONEYBOX = 'pig';
    const CARDS = 'cards';
    const GAME_HISTORY = 'book';
    const RATING = 'rating';
    const FRIEND_PHOTO = 'photo';
    const WALLET = 'wallet';
    const DEALER = 'dealer';

    /**
     * Привилегии товара VIP
     *
     * @var array
     */
    static protected $vip = array(
        self::CHESS_BOARD,
        self::CHESS_CLOCK,
        self::POOL,
        self::FILLER_BOARD,
        self::BACKGAMMON_BOARD,
        self::MONEYBOX,
        self::CARDS,
        self::DEALER,
        self::RATING
    );

    /**
     * Проверка наличия товара в сессии клиента
     *
     * @static
     *
     * @param string $name Наименование товара
     * @param string $sid  Идентификатор сессии клиента, если не указан, возвращает ответ для текущего клиента
     *
     * @return bool
     */
    static public function hasItem($name, $sid = null)
    {
        //Получение данных сессии клиента (пользователя)
        if (null !== $sid) {
            //TODO: придумать решения без класса сессии из application (это ведь библиотека!!!)
            $session = new App_Model_Session_User();
            $session->find($sid);
        } else {
            $session = Core_Session::getInstance()->get(Core_Session::USER_NAMESPACE);
        }

        //Проверка наличия возможности работы со списком товаров пользователя
        if ($session instanceof Core_Shop_Items_Interface) {
            //Проверка наличия товара у пользователя
            if (in_array($name, self::$vip) && $session->hasItem(self::VIP)) {
                return true;
            }
            return $session->hasItem($name);
        } else {
            return false;
        }
    }

    /**
     * Проверка использование товара в игре
     *
     * @static
     * @param string $itemName
     * @param string|null $gameName
     * @return bool
     */
    static public function isUseInGame($itemName, $gameName = null)
    {
        //Получаем наимененвание игры
        if (null === $gameName) {
            //Попытка получить данные сессии игры из реестра
            try {
                $session = Core_Session::getInstance()->get(Core_Session::GAME_NAMESPACE);
                if (!$session && !$session->name) {
                    return true;
                } else {
                    $gameName = $session->name;
                }
            }
            catch (Exception $e) {
                return true;
            }
        }

        //Описание связей товаров с играми
        $itemLinks = array(
            self::CHESS_BOARD => array(Core_Game_Chess::GAME_NAME, Core_Game_Checkers::GAME_NAME),
            self::GLASSES => array(Core_Game_Durak::GAME_NAME, Core_Game_DurakTransfer::GAME_NAME),
            self::FILLER_BOARD => array(Core_Game_Filler_Rectangle::GAME_NAME, Core_Game_Filler_Sota::GAME_NAME),
            self::BACKGAMMON_BOARD => array(Core_Game_Backgammon::GAME_NAME),
            self::CARDS => array(Core_Game_Durak::GAME_NAME, Core_Game_DurakTransfer::GAME_NAME),
            self::DEALER => array(),
            self::POOL => array()
        );

        //Проверяем возможность использование указанного товара в текущей игре
        if (!isset($itemLinks[$itemName])) {
            //Если в списке товара нет, он используется во всех играх
            return true;
        }
        $games = $itemLinks[$itemName];
        return in_array($gameName, $games);
    }

}
