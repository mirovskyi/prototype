<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 07.04.12
 * Time: 13:03
 *
 * Событие закрытия игрового стола для всех игроков (выход из игры создателя)
 */
class App_Service_Events_Gameclose extends App_Service_Events_Abstract
{

    /**
     * Системное имя события
     */
    const EVENT_TYPE = 'gameclose';

    /**
     * Флаг одиночного события (возможность добавления множества подобных событий)
     *
     * @var bool
     */
    protected $_single = true;

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
