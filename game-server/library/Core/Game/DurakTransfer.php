<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 02.07.12
 * Time: 12:22
 *
 * Класс описывающий игру "Дурак переводной"
 */
class Core_Game_DurakTransfer extends Core_Game_Durak
{

    /**
     * Системное имя игры
     */
    const GAME_NAME = 'durak_transfer';

    /**
     * Список игроков, которые показали козырную карту при переводе
     *
     * @var array
     */
    protected $_showTrumpCard = array();


    /**
     * Получение имени игры
     *
     * @return string
     */
    public function getName()
    {
        return self::GAME_NAME;
    }

    /**
     * Формирование данных новой партии
     */
    public function generate()
    {
        parent::generate();

        //Очищаем список игроков показавших козырную карту при переводе
        $this->clearShowTrumpCard();
    }

    /**
     * Получение объекта розыгрыша
     *
     * @return Core_Game_DurakTransfer_Process
     */
    public function getProcess()
    {
        return parent::getProcess();
    }

    /**
     * Добавление игрока в список показавших козырную карту при переводе
     *
     * @param Core_Game_Durak_Players_Player|string $player
     */
    public function addShowTrumpCardPlayer($player)
    {
        if ($player instanceof Core_Game_Durak_Players_Player) {
            $player = $player->getSid();
        }
        $this->_showTrumpCard[] = $player;
    }

    /**
     * Проверка показа игроком козврной карты при переводе
     *
     * @param Core_Game_Durak_Players_Player|string $player
     *
     * @return bool
     */
    public function isPlayerShowTrumpCard($player)
    {
        if ($player instanceof Core_Game_Durak_Players_Player) {
            $player = $player->getSid();
        }
        return in_array($player, $this->_showTrumpCard);
    }

    /**
     * Очистка списка игроков показавших козырную карту при переводе
     */
    public function clearShowTrumpCard()
    {
        $this->_showTrumpCard = array();
    }

    /**
     * Подкинуть карты в розыгрыш
     *
     * @param Core_Game_Durak_Players_Player|string     $player Игрок, подкидывающий карты
     * @param Core_Game_Durak_Cards_Card[]|array|string $cards  Список карт
     * @param bool                                      $show   Флаг показа карты при переводе
     *
     * @return void
     */
    public function throwCards($player, $cards, $show = false)
    {
        if (is_string($player)) {
            $player = $this->getPlayersContainer()->getPlayer($player);
        }
        //Проверка игрока от которого пришел запрос подкинуть картy
        if ($this->getPlayersContainer()->getDefenderPlayer()->getSid() == $player->getSid()) {
            //Отбивающий игрок подкинул карту - перевод карт
            $this->transfer($cards, $show);
        } else {
            //Подбрасывание карт в розыгрыш
            parent::throwCards($player, $cards);
        }
    }

    /**
     * Перевод карт
     *
     * @param Core_Game_Durak_Cards_Card[]|array|string $cards  Список карт
     * @param bool                                      $show   Флаг показа карты при переводе
     *
     * @throws Core_Game_DurakTransfer_Exception
     */
    public function transfer($cards, $show = false)
    {
        //Преобразование данных о картах в
        if (is_string($cards)) {
            $cards = explode(',', $cards);
        }
        foreach($cards as $key => $card) {
            if (is_string($card)) {
                $cards[$key] = Core_Game_Durak_Cards_Card::create($card);
            }
        }
        //Проверка возможности перевода
        if (!$this->getProcess()->canTransfer($cards, $show)) {
            throw new Core_Game_DurakTransfer_Exception('Player can\'t transfer cards', 3014, Core_Exception::USER);
        }
        //Сохраняем остаток времени на партию текущего отбивающего игрока
        $defender = $this->getPlayersContainer()->getDefenderPlayer();
        $defender->setGametime($this->getPlayerGametime($defender));
        //Перевод карт
        $this->getProcess()->transfer($cards, $show);
    }

    /**
     * Инициализация объекта розыгрыша
     */
    protected function _initProcess()
    {
        //Получаем количество игроков за столом
        $playersCount = count($this->getPlayersContainer());
        //Инициализация объекта соответствующего розыгрыша (одиночный или на пары)
        if ($playersCount == 4) {
            $this->_process = new Core_Game_DurakTransfer_Process_Doubles($this);
        } else {
            $this->_process = new Core_Game_DurakTransfer_Process_Single($this);
        }
        //Очистка временных данных предыдущего розыгрыша
        $this->_processHistory = null;
    }

}
