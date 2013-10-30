<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 07.03.12
 * Time: 14:09
 *
 * Класс описания события увеличения ставки в игре
 */
class App_Service_Events_Bet extends App_Service_Events_Abstract
{

    /**
     * Тип события
     */
    const EVENT_TYPE = 'bet';

    /**
     * Сумма ставки
     *
     * @var int
     */
    protected $_betAmount;

    /**
     * Список игроков, согласных с событием повышения ставки
     *
     * @var array
     */
    protected $_confirmPlayers = array();

    /**
     * Флаг одиночного события (возможность добавления множества подобных событий)
     *
     * @var bool
     */
    protected $_single = true;


    /**
     * __construct
     *
     * @param int $betAmount Сумма ставки
     * @param string|null $currentUserSid Идентификатор сессии текущего игрока
     */
    public function __construct($betAmount, $currentUserSid = null)
    {
        $this->setBetAmount($betAmount);
        parent::__construct($currentUserSid);
    }

    /**
     * Получение типа события
     *
     * @return string
     */
    public function getType()
    {
        return self::EVENT_TYPE;
    }

    /**
     * Получение уникального имени события
     *
     * @return string
     */
    public function getName()
    {
        return $this->name($this->getBetAmount());
    }

    /**
     * Формирование уникального имени события
     *
     * @static
     * @param $betAmount
     * @return string
     */
    public static function name($betAmount)
    {
        return self::EVENT_TYPE . ':' . $betAmount;
    }

    /**
     * Установка суммы ставки
     *
     * @param $amount
     * @return App_Service_Events_Bet
     */
    public function setBetAmount($amount)
    {
        $this->_betAmount = $amount;
        return $this;
    }

    /**
     * Получение суммы ставки
     *
     * @return int
     */
    public function getBetAmount()
    {
        return $this->_betAmount;
    }

    /**
     * Получение списка согласившихся игроков
     *
     * @return array
     */
    public function getConfirmPlayers()
    {
        return $this->_confirmPlayers;
    }

    /**
     * Установка согласия пользоватля с событием повышения ставки
     *
     * @param int $sid
     * @return App_Service_Events_Bet
     */
    public function playerConfirm($sid)
    {
        if (!in_array($sid, $this->_confirmPlayers)) {
            $this->_confirmPlayers[] = $sid;
        }
        return $this;
    }

    /**
     * Проверка согласия пользователя
     *
     * @param string $sid
     * @return bool
     */
    public function isPlayerConfirm($sid)
    {
        return in_array($sid, $this->_confirmPlayers);
    }

    /**
     * Обработка события
     *
     * @return void
     */
    public function handle()
    {
        //Пользователь оповещен
        $this->notifyPlayer($this->getCurrentUserSid());

        //Пользователь согласился
        $this->playerConfirm($this->getCurrentUserSid());

        //Проверка согласия всех пользователей
        if (count($this->getGameObject()->getPlayersContainer()) == count($this->getConfirmPlayers())) {
            //Инкремент номера обновления
            $this->getGameObject()->incCommand();
            //Увеличиваем сумму в ставке игры
            $this->getGameObject()->setBet($this->getBetAmount());
            //Добавление пустой анимации в историю обновления игры (чтобы не поламать порядок анимирования)
            $this->getGameObject()->addEmptyAnimation();
            //Изменение статуса события
            $this->_workedOut = true;
        }
    }

    /**
     * Обработка завершения работы события
     *
     * @return void
     */
    public function destroy()
    {
        //Cоздание события отмены увеличения ставки
        $betCancel = new App_Service_Events_BetCancel($this->getBetAmount(), $this->getCurrentUserSid());
        //Оповещение только тех пользователей которые не ответили на событие
        foreach($this->getGameObject()->getPlayersContainer() as $player) {
            if ($this->isPlayerConfirm($player->sid) || $this->getCurrentUserSid() == $player->sid) {
                //Добавление польователя в список уже оповещенных
                $betCancel->notifyPlayer($player->sid);
            }
        }
        //Установка события в игре
        $this->getGameObject()->addEvent($betCancel);

        //Удаление события
        $this->getGameObject()->deleteEvent($this->getName());
    }

    /**
     * Получение данных события в виде строки
     *
     * @return string
     */
    public function __toString()
    {
        //Получение объекта текущего игрока
        $player = $this->_getGameSession()->getData()->getPlayersContainer()->getPlayer($this->getCurrentUserSid());
        //Проверка участия пользователя в игре (проверка на наблюдателя)
        if (null === $player) {
            //Наблюдатель, событие не отображается
            return '';
        }
        //Проверка состояния события
        if ($this->isWorkedOut()) {
            return '';
        }
        //Проверка наличия пользователя в списке оповещенных
        if (!$this->isPlayerNotified($this->getCurrentUserSid())) {
            //Блокируем и получаем данные сессии игры
            $this->_getGameSession()->lockAndUpdate();
            //Добавление пользователя в список оповещенных о событии (сервер оповещает клиента только один раз)
            $event = $this->_getGameSession()->getData()->getEvent($this->getName());
            $event->notifyPlayer($this->getCurrentUserSid());
            //Сохраняем и разблокируем данные сессии игры
            $this->_getGameSession()->saveAndUnlock();
            //Отдаем контент события
            return $this->_toXml();
        }

        return '';
    }

    /**
     * Получение данных события в виде XML
     *
     * @return string
     */
    private function _toXml()
    {
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startDocument();
        $xmlWriter->startElement('bet');
        $xmlWriter->startElement('amount');
        $xmlWriter->text($this->getBetAmount());
        $xmlWriter->endElement();
        $xmlWriter->endElement();
        $xmlWriter->endDocument();
        return $xmlWriter->flush(false);
    }
}