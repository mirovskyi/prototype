<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 22.08.12
 * Time: 18:50
 *
 * Событие закрытия игрового стола для игрока (недостаточно средств у игрока для продолжения игры)
 */
class App_Service_Events_UserGameclose  extends App_Service_Events_Abstract
{

    /**
     * Системное имя события
     */
    const EVENT_TYPE = 'usergameclose';

    /**
     * Флаг одиночного события (возможность добавления множества подобных событий)
     *
     * @var bool
     */
    protected $_single = false;

    /**
     * Идентификатор пользователя, для которого создается событие
     *
     * @var string
     */
    protected $_user;

    /**
     * __construct
     *
     * @param string|null $currentUserSid Идентификатор текущего пользователя
     */
    public function __construct($currentUserSid = null)
    {
        parent::__construct($currentUserSid);

        //Установка пользователя для которого необходимо установить событие
        $this->setUser($currentUserSid);
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
        return $this->name($this->getUser());
    }

    /**
     * Формирование уникального имени события
     *
     * @static
     *
     * @param string $userSid Идентификатор пользователя, для которого создано событие
     *
     * @return string
     */
    public static function name($userSid)
    {
        return self::EVENT_TYPE . $userSid;
    }

    /**
     * Установка идентификатора пользователя для которого действует событие
     *
     * @param string $sid
     */
    public function setUser($sid)
    {
        $this->_user = $sid;
    }

    /**
     * Получение идентификатора пользователя для которого действует событие
     *
     * @return string
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Обработка события
     *
     * @return void
     */
    public function handle()
    {
        $this->notifyPlayer($this->getCurrentUserSid());
    }

    /**
     * Завершение события
     *
     * @return void
     */
    public function destroy()
    {
        $this->getGameObject()->deleteEvent($this->getName());
    }

    /**
     * Получение данных события в виде строки
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->getCurrentUserSid() == $this->getUser() && !$this->isPlayerNotified($this->getCurrentUserSid())) {
            //Блокируем и получаем данные сессии игры
            $this->_getGameSession()->lockAndUpdate();
            //Добавление пользователя в список оповещенных о событии (сервер оповещает клиента только один раз)
            $event = $this->_getGameSession()->getData()->getEvent($this->getName());
            $event->notifyPlayer($this->getCurrentUserSid());
            //Сохраняем и разблокируем данные сессии игры
            $this->_getGameSession()->saveAndUnlock();
            //Отдаем контент события
            return $this->_toXml();
        } else {
            return '';
        }
    }

    /**
     * Получение события в виде XML
     *
     * @return string
     */
    private function _toXml()
    {
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startElement('gameclose');
        $xmlWriter->endElement();
        return $xmlWriter->flush(false);
    }
}