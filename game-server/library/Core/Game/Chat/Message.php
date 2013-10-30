<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 14.03.12
 * Time: 12:03
 *
 * Класс сообщения чата
 */
class Core_Game_Chat_Message
{

    /**
     * Ключ сообщения для всех пользователей
     */
    const ALL_PLAYERS = 'all';

    /**
     * Текст сообщения
     *
     * @var string
     */
    protected $_t;

    /**
     * Имя отправителя
     *
     * @var string
     */
    protected $_s;

    /**
     * Имя получателя
     *
     * @var string
     */
    protected $_r;

    /**
     * Дата добавления сообщения (в unix формате)
     *
     * @var int
     */
    protected $_d;


    /**
     * Создание нового сообщения чата
     *
     * @param string $text Текст сообщения
     * @param string $sender Имя отправителя
     * @param string $recipient Имя получателя
     */
    public function __construct($text, $sender, $recipient)
    {
        $this->setText($text)
             ->setSender($sender)
             ->setRecipient($recipient)
             ->setDatetime(date('U'));
    }

    /**
     * Установка текста сообщения
     *
     * @param string $text
     * @return Core_Game_Chat_Message
     */
    public function setText($text)
    {
        $this->_t = $text;
        return $this;
    }

    /**
     * Получение текста сообщения
     *
     * @return string
     */
    public function getText()
    {
        return $this->_t;
    }

    /**
     * Установка имени отправителя
     *
     * @param string $senderName
     * @return Core_Game_Chat_Message
     */
    public function setSender($senderName)
    {
        $this->_s = $senderName;
        return $this;
    }

    /**
     * Получение имени отправителя
     *
     * @return string
     */
    public function getSender()
    {
        return $this->_s;
    }

    /**
     * Установка имени получателя
     *
     * @param string $recipientName
     * @return Core_Game_Chat_Message
     */
    public function setRecipient($recipientName)
    {
        $this->_r = $recipientName;
        return $this;
    }

    /**
     * Получение имени получателя
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->_r;
    }

    /**
     * Установка даты/времени добавления сообщения
     *
     * @param int|string $datetime Дата/время добавления сообщения
     *
     * @return Core_Game_Chat_Message
     */
    public function setDatetime($datetime)
    {
        if (preg_match('/^\d+$/', $datetime)) {
            $date = new DateTime();
            $date->setTimestamp($datetime);
        } else {
            $date = new DateTime($datetime);
        }

        $this->_d = $date->format('U');
        return $this;
    }

    /**
     * Получение даты/времени добавления сообщения
     *
     * @param string $format Формат даты
     *
     * @return string
     */
    public function getDatetime($format = 'U')
    {
        $date = new DateTime();
        $date->setTimestamp($this->_d);

        return $date->format($format);
    }

    /**
     * Проверка окончания срока актуальности сообщения
     *
     * @return bool
     */
    public function isExpired()
    {
        //Получаем текущее значение времени в unix формате
        $currentDateTime = date('U');
        //Если с момента добавления сообщения прошел час - сообщение устарело
        $diff = $currentDateTime - $this->_d;
        if ($diff > (60 * 60)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->getRecipient() && $this->getRecipient() != self::ALL_PLAYERS) {
            $message = $this->getSender() . ' (личное): ' . $this->getText();
        } else {
            $message = $this->getSender() . ': ' . $this->getText();
        }
        return $message;
    }

}
