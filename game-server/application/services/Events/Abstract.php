<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 07.03.12
 * Time: 14:46
 *
 * Абстракция события в игре
 */
abstract class App_Service_Events_Abstract implements Core_Game_Event
{

    /**
     * Объект игры, к которой применяется событие
     *
     * @var Core_Game_Abstract
     */
    protected $_gameObject;

    /**
     * Идентификатор сессии текущего пользователя
     *
     * @var string
     */
    protected $_currentUserSid;

    /**
     * Список оповещенных игроков
     *
     * @var array
     */
    protected $_notifiedPlayers = array();

    /**
     * Флаг завершения обработки события
     *
     * @var bool
     */
    protected $_workedOut = false;

    /**
     * Флаг одиночного события (возможность добавления множества подобных событий)
     *
     * @var bool
     */
    protected $_single = false;

    /**
     * Время создания события
     *
     * @var int
     */
    protected $_runtime;

    /**
     * Время таймаута события в секундах
     *
     * @var int
     */
    protected $_timeout = 30;


    /**
     * __construct
     *
     * @param string|null $currentUserSid Идентификатор текущего пользователя
     */
    public function __construct($currentUserSid = null)
    {
        //Установка текущего времени создания события
        $this->_runtime = time();

        if (null !== $currentUserSid) {
            $this->setCurrentUserSid($currentUserSid);
        }
    }

    /**
     * Magic method __sleep
     *
     * @return array
     */
    public function __sleep()
    {
        //Список полей текущего экземпляра класса
        $properties = array();

        //Формирование списка полей
        $r = new ReflectionObject($this);
        foreach($r->getProperties() as $property) {
            $properties[] = $property->getName();
        }

        //Удаление из списка полей, которые не нужно сериализовать
        return array_diff($properties, array(
            '_gameObject',
            '_currentUserSid'
        ));
    }

    /**
     * Установка идентификатора текущего пользователя
     *
     * @param string $sid
     * @return App_Service_Events_Abstract
     */
    public function setCurrentUserSid($sid)
    {
        $this->_currentUserSid = $sid;
        return $this;
    }

    /**
     * Получение идентификатора текущего пользователя
     *
     * @return string
     */
    public function getCurrentUserSid()
    {
        if (null === $this->_currentUserSid) {
            //Получение идентификатора сессии текущего пользователя из реестра
            if (Core_Session::getInstance()->has(Core_Session::USER_NAMESPACE)) {
                $this->setCurrentUserSid(Core_Session::getInstance()->get(Core_Session::USER_NAMESPACE)->sid);
            }
        }
        return $this->_currentUserSid;
    }

    /**
     * Установка списка оповещенных игроков
     *
     * @param array $playersSid Идентификатор сессии пользователя
     * @return App_Service_Events_Abstract
     */
    public function setNotifiedPlayers(array $playersSid)
    {
        $this->_notifiedPlayers = $playersSid;
        return $this;
    }

    /**
     * Получение списка оповещенных игроков
     *
     * @return array
     */
    public function getNotifiedPlayers()
    {
        return $this->_notifiedPlayers;
    }

    /**
     * Добавление игрока в список оповещенных
     *
     * @param string $playerSid Идентификатор сессии пользователя
     * @return App_Service_Events_Abstract
     */
    public function notifyPlayer($playerSid)
    {
        if (!in_array($playerSid, $this->_notifiedPlayers)) {
            $this->_notifiedPlayers[] = $playerSid;
        }
        return $this;
    }

    /**
     * Удаление игрока из списка оповещенных
     *
     * @param string $playerSid Идентификатор сессии пользователя
     */
    public function clearPlayerNotification($playerSid)
    {
        $index = array_search($playerSid, $this->_notifiedPlayers);
        if (false !== $index) {
            unset($this->_notifiedPlayers[$index]);
        }
    }

    /**
     * Проверка оповещения пользователя о событии
     *
     * @param string $playerSid Идентификатор сессии пользователя
     * @return bool
     */
    public function isPlayerNotified($playerSid)
    {
        return in_array($playerSid, $this->_notifiedPlayers);
    }

    /**
     * Установка объекта игры, к которой применяется событие
     *
     * @param Core_Game_Abstract $game
     * @return App_Service_Events_Abstract
     */
    public function setGameObject(Core_Game_Abstract $game)
    {
        $this->_gameObject = $game;
        return $this;
    }

    /**
     * Получение объекта игры, к которой применяется событие
     *
     * @return Core_Game_Abstract
     */
    public function getGameObject()
    {
        return $this->_gameObject;
    }

    /**
     * Проверка возможности создания множества подобных событий
     *
     * @abstract
     * @return bool
     */
    public function isSingle()
    {
        return $this->_single;
    }

    /**
     * Проверка завершения работы события
     *
     * @return bool
     */
    public function isWorkedOut()
    {
        if ($this->_workedOut) {
            return true;
        }

        //Проверка таймаута
        if ($this->_timeout) {
            if ($this->_runtime + $this->_timeout < time()) {
                $this->_workedOut = true;
            }
        }

        return $this->_workedOut;
    }

    /**
     * Получение объекта модели сессии игры
     *
     * @return App_Model_Session_Game|bool
     */
    protected function _getGameSession()
    {
        if (!Core_Session::getInstance()->has(Core_Session::GAME_NAMESPACE)) {
            return false;
        }

        return Core_Session::getInstance()->get(Core_Session::GAME_NAMESPACE);
    }
}
