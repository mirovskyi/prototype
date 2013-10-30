<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.03.12
 * Time: 18:22
 *
 * Класс описания события предложения ничьи
 */
class App_Service_Events_Draw extends App_Service_Events_Abstract
{

    /**
     * Наименование события
     */
    const EVENT_TYPE = 'draw';

    /**
     * Идентификатор сессии пользователя, который предложил ничью
     *
     * @var string
     */
    protected $_drawUser;

    /**
     * Список игроков согласных на ничью
     *
     * @var array
     */
    protected $_confirm = array();


    /**
     * __construct
     *
     * @param string|null $currentUserSid Идентификатор текущего пользователя
     */
    public function __construct($currentUserSid = null)
    {
        parent::__construct($currentUserSid);

        $this->addConfirm($this->getCurrentUserSid());
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
        return $this->name();
    }

    /**
     * Формирование уникального имени события
     *
     * @static
     * @return string
     */
    public static function name()
    {
        return self::EVENT_TYPE;
    }

    /**
     * Установка идентификатора сессии пользователя, предложившего ничью
     *
     * @param string $sid
     */
    public function setDrawUser($sid)
    {
        $this->_drawUser = $sid;
    }

    /**
     * Получение идентификатора пользователя, предложившего ничью
     *
     * @return string
     */
    public function getDrawUser()
    {
        return $this->_drawUser;
    }

    /**
     * Установка списка согласившихся с ничьей пользователей
     *
     * @param array $playersSid
     */
    public function setConfirmPlayers(array $playersSid)
    {
        $this->_confirm = $playersSid;
    }

    /**
     * Добавление согласившегося с ничьей пользователя
     *
     * @param $playerSid
     */
    public function addConfirm($playerSid)
    {
        if (!in_array($playerSid, $this->_confirm)) {
            $this->_confirm[] = $playerSid;
        }
    }

    /**
     * Получение списка согласившихся с ничьей пользователей
     *
     * @return array
     */
    public function getConfirms()
    {
        return $this->_confirm;
    }

    /**
     * Обработка события
     *
     * @return void
     */
    public function handle()
    {
        //Оповещение текущего игрока
        $this->notifyPlayer($this->getCurrentUserSid());
        //Принятие согласие на ничью от пользователя
        $this->addConfirm($this->getCurrentUserSid());

        //Проверка согласия всех пользователей
        if (count($this->getGameObject()->getPlayersContainer()) == count($this->getConfirms())) {
            //Завершение игры (изменение статуса)
            $this->getGameObject()->setStatus(Core_Game_Abstract::STATUS_FINISH);
            //Установка состояния игроков
            $this->getGameObject()->setDraw();
            //Для шахмат устанавливаем событие ничьи в объекте шахматной доски
            if ($this->getGameObject() instanceof Core_Game_Chess) {
                $this->getGameObject()->getChessBoard()->setEvent(Core_Game_Chess_Board::DRAW);
            }
            //Завершение события
            $this->destroy();
            //Обновление состояния игры
            $this->getGameObject()->updateGameState();
            //Изменение статуса события
            $this->_workedOut = true;
        }
    }

    /**
     * Завершение события
     *
     * @return void
     */
    public function destroy()
    {
        //Удаление события из игры
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
        $xmlWriter->startElement('draw');
        $user = new App_Model_Session_User();
        $user->find($this->getDrawUser());
        $xmlWriter->writeAttribute('user', $user->getSocialUser()->getName());
        $xmlWriter->endElement();
        return $xmlWriter->flush(false);
    }
}
