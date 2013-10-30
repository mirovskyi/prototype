<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 10.04.12
 * Time: 14:01
 *
 * Событие отмены поднятия ставки
 */
class App_Service_Events_BetCancel extends App_Service_Events_Abstract
{

    /**
     * Имя события
     */
    const EVENT_TYPE = 'bet_cancel';

    /**
     * Таймаута
     *
     * @var int
     */
    protected $_timeout = 0; //Без таймаута

    /**
     * Сумма ставки
     *
     * @var int
     */
    protected $_betAmount;

    /**
     * Флаг одиночного события (возможность добавления множества подобных событий)
     *
     * @var bool
     */
    protected $_single = true;

    /**
     * __construct
     *
     * @param $betAmount
     * @param null $currentUserSid
     */
    public function __construct($betAmount, $currentUserSid = null)
    {
        parent::__construct($currentUserSid);
        $this->setBetAmount($betAmount);
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
     * Получение имени события
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
     * Обработка события
     *
     * @return void
     */
    public function handle()
    {
        //Добавление пользователя в список оповещенных
        $this->notifyPlayer($this->getCurrentUserSid());

        //Проверка оповещения всех игроков
        $playersCount = count($this->getGameObject()->getPlayersContainer());
        if (count($this->getNotifiedPlayers()) >= $playersCount) {
            //Установка флага завершения события
            $this->_workedOut = true;
            //Тут же удаляем
            $this->destroy();
        }
    }

    /**
     * Завершение события
     *
     * @return void
     */
    public function destroy()
    {
        //Удаленин события
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
        //Проверка наличия игрока в списке оповещенных
        if ($this->isPlayerNotified($this->getCurrentUserSid())) {
            return '';
        }

        //Проверка доступности данных сессии игры
        if ($this->_getGameSession()) {
            //Блокировка и получение данных сессии игры
            $this->_getGameSession()->lockAndUpdate();
            //Обработка события
            $this->handle();
            //Сохраняем и разблокируем данные сессии
            $this->_getGameSession()->saveAndUnlock();
        }

        //Возвращаем контент события
        return $this->_toXml();
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
        $xmlWriter->writeAttribute('cancel', '1');
        $xmlWriter->startElement('amount');
        $xmlWriter->text($this->getBetAmount());
        $xmlWriter->endElement();
        $xmlWriter->endElement();
        $xmlWriter->endDocument();
        return $xmlWriter->flush(false);
    }
}
