<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 29.02.12
 * Time: 17:14
 *
 * Реализация чата в играх
 */
class Core_Game_Chat
{

    /**
     * Список пользователей в чате
     *
     * @var Core_Game_Chat_Player[]
     */
    protected $_players = array();

    /**
     * Список сообщений чата
     *
     * @var Core_Game_Chat_Message[]
     */
    protected $_messages = array();


    /**
     * Обработка объекта после unserialize
     */
    public function __wakeup()
    {
        foreach($this->_players as $sid => $player) {
            $player->setSid($sid);
        }
    }

    /**
     * Получение списка пользователей в чате
     *
     * @return Core_Game_Chat_Player[]
     */
    public function getPlayers()
    {
        return $this->_players;
    }

    /**
     * Получение списка имен пользователей чата
     *
     * @return array
     */
    public function getPlayersName()
    {
        $names = array();
        foreach($this->_players as $player) {
            $names[] = $player->getName();
        }

        return $names;
    }

    /**
     * Получение объекта пользователя в чате игры
     *
     * @param string $sid Идентификатор сессии пользователя
     * @return Core_Game_Chat_Player
     * @throws Core_Game_Chat_Exception
     */
    public function getPlayer($sid)
    {
        if (isset($this->_players[$sid])) {
            return $this->_players[$sid];
        }

        throw new Core_Game_Chat_Exception('User ' . $sid . ' does not exists in the game chat');
    }

    /**
     * Проверка налчичия игрока в чате
     *
     * @param string $sid Идентификатор сессии игрока
     *
     * @return bool
     */
    public function hasPlayer($sid)
    {
        return isset($this->_players[$sid]);
    }

    /**
     * Добавление пользователя в чат
     *
     * @param Core_Game_Chat_Player $player
     * @return Core_Game_Chat
     */
    public function addPlayer(Core_Game_Chat_Player $player)
    {
        $this->_players[$player->getSid()] = $player;
        return $this;
    }

    /**
     * Удаление пользователя из чата
     *
     * @param Core_Game_Chat_Player|string $player
     * @return Core_Game_Chat
     */
    public function dellPlayer($player)
    {
        if ($player instanceof Core_Game_Chat_Player) {
            $player = $player->getSid();
        }

        unset($this->_players[$player]);
        return $this;
    }

    /**
     * Получение текущего индекса сообщения (индекса последнего сообщения)
     *
     * @return int
     */
    public function getCurrentId()
    {
        return count($this->_messages);
    }

    /**
     * Получение всех сообщений чата
     *
     * @return Core_Game_Chat_Message[]
     */
    public function getMessages()
    {
        //Получение всех сообщений чата
        return $this->_messages;
    }

    /**
     * Получение сообщение по его пордковому номеру
     *
     * @param int $index
     *
     * @throws Core_Game_Chat_Exception
     * @return Core_Game_Chat_Message
     */
    public function getMessage($index)
    {
        if (!isset($this->_messages[$index])) {
            throw new Core_Game_Chat_Exception('Message with index ' . $index . ' does not exists');
        }

        return $this->_messages[$index];
    }

    /**
     * Получение разницы сообщений между последним и сообщением с указанным индексом
     *
     * @param int $id Индекс последнего полученного пользователем сообщения
     * @param Core_Game_Chat_Player|string $player Объект пользователя чата | идентификатор сессии пользователя
     * @return Core_Game_Chat_Message[]
     */
    public function getMessagesDiff($id, $player)
    {
        //Список сообщений для отображения в чате пользователя
        $result = array();

        //Проверка наличия новых сообщений
        if ($id >= $this->getCurrentId()) {
            //Новых сообщений нет
            return $result;
        }

        //Получаем объект игрока в чате
        if (!$player instanceof Core_Game_Chat_Player) {
           $player = $this->getPlayer($player);
        }

        //Формирование сообщений для чата пользователя, начиная со следующего сообщения после указанного индекса
        for($i = $id; $i < $this->getCurrentId(); $i++) {
            //Получение сообщения
            $message = $this->getMessage($i);
            //Проверка актуальности сообщения
            if ($message->isExpired()) {
                //Сообщение устарело
                continue;
            }

            //Если сообщения преднозначено для всех пользователей, добавляем его в результат
            if ($message->getRecipient() == Core_Game_Chat_Message::ALL_PLAYERS) {
                $result[] = $message;
            }
            //Если сообщение было написано текущим пользователем, добавляем его в результат
            elseif ($message->getSender() == $player->getName()) {
                $result[] = $message;
            }
            //Если сообщение было написано для текущего пользователя, добавляем его в результат
            elseif ($message->getRecipient() == $player->getName()) {
                $result[] = $message;
            }
        }

        return $result;
    }

    /**
     * Добавление сообщения в чат
     *
     * @param string $text Текст сообщения
     * @param Core_Game_Chat_Player|string $sender Объект пользователя чата либо идентификатор сессии пользователя
     * @param Core_Game_Chat_Player|string $recipient Объект пользователя чата, либо идентификатор сессии пользователя, либо указатель на всех пользователей ALL_PLAYERS
     */
    public function addMessage($text, $sender, $recipient = null)
    {
        //Имя отправителя
        if ($sender instanceof Core_Game_Chat_Player) {
            $sender = $sender->getName();
        } else {
            $sender = $this->getPlayer($sender)->getName();
        }

        //Получатель
        if (null === $recipient) {
            $recipient = Core_Game_Chat_Message::ALL_PLAYERS;
        } elseif ($recipient instanceof Core_Game_Chat_Player) {
            $recipient = $recipient->getName();
        } elseif ($recipient != Core_Game_Chat_Message::ALL_PLAYERS) {
            $recipient = $this->getPlayer($recipient)->getName();
        }

        //Проверка наличия получателя в тексте сообщения
        if ($recipient != Core_Game_Chat_Message::ALL_PLAYERS) {
            $recipientText = $recipient . ': ';
            if (substr($text, 0, strlen($recipientText)) != $recipientText) {
                //В начале текста адресат не указан, сообщение для всех пользователей
                $recipient = Core_Game_Chat_Message::ALL_PLAYERS;
            } else {
                //Удаляем имя адресата из текста сообщения
                $text = substr($text, strlen($recipientText));
            }
        }

        //Создание сообщения
        $message = new Core_Game_Chat_Message($text, $sender, $recipient);

        //Добавляем сообщение
        $this->_messages[] = $message;
    }

    /**
     * Получение данных чата в виде XML
     *
     * @param Core_Game_Chat_Player|string $player Объект пользователя чата | идентификатор сессии пользователя
     * @param int $messageId Индекс последнего полученного пользователем сообщения
     * @param bool $showPlayers Флаг получения списка игроков в чате
     * @return string
     */
    public function saveXml($player, $messageId = 0, $showPlayers = true)
    {
        //Получаем список сообщений
        $messages = $this->getMessagesDiff($messageId, $player);

        //Идентификатор текущего пользователя
        if ($player instanceof Core_Game_Chat_Player) {
            $currentSid = $player->getSid();
        } else {
            $currentSid = $player;
        }

        //Формирование XML
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();

        //Добавление списка пользователей
        if ($showPlayers) {
            $xmlWriter->startElement('users');
            foreach($this->getPlayers() as $player) {
                //Текущего пропускаем, чтобы не видеть себя же в списке пользователей
                if ($player->getSid() == $currentSid) {
                    continue;
                }
                //Формирование данных пользователя
                $xmlWriter->startElement('user');
                $xmlWriter->writeAttribute('sid', $player->getSid());
                $xmlWriter->writeAttribute('spectator', $player->getType());
                $xmlWriter->writeElement('name', $player->getName());
                $xmlWriter->endElement();
            }
            $xmlWriter->endElement();
        }

        //Добавление сообщений
        $xmlWriter->startElement('messages');
        foreach($messages as $message) {
            $xmlWriter->writeElement('m', $message);
        }
        $xmlWriter->endElement();

        //Возвращаем сформированный XML
        return $xmlWriter->flush(false);
    }

}
