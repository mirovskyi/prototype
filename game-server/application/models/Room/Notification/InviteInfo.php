<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.08.12
 * Time: 11:24
 *
 * Класс описания оповещения пользователя, создателя приватного стола, о принятии/отказе приглашения оппонентов.
 */
class App_Model_Room_Notification_InviteInfo implements Core_Protocol_NotificationInterface, App_Model_Interface
{

    /**
     * Префикс ключа записи
     */
    const KEY_PREFIX = 'iin:';

    /**
     * Идентификатор сессии пользователя, для которого необходимо оповещение
     *
     * @var string
     */
    protected $_userSid;

    /**
     * Идентификатор сессии приватной игры (созданной пользователем)
     *
     * @var string
     */
    protected $_gameSid;

    /**
     * Список идентификаторов сессий пользователей, которые приняли приглашение за приватный игровой стол
     *
     * @var array
     */
    protected $_confirm = array();

    /**
     * Список идентификаторов сессий пользователей, которые отвергли приглашение за приватный игровой стол
     *
     * @var array
     */
    protected $_decline = array();

    /**
     * Флаг оповещения пользователя (создателя игры) о соглашении|отказе оппонента
     *
     * @var bool
     */
    protected $_notified = false;

    /**
     * Флаг закрытия окна уведомления
     *
     * @var bool
     */
    protected $_closed = false;

    /**
     * Модель доступа к данным хранилица
     *
     * @var App_Model_Mapper_Storage
     */
    protected $_mapper;

    /**
     * __construct
     *
     * @param array $options
     */
    public function __construct($options = null)
    {
        $this->setOptions($options);
    }

    /**
     * Magic method __sleep
     *
     * @return array
     */
    public function __sleep()
    {
        //Формируем список своиств класса без объекта работы с хранилищем (_mapper)
        $properties = array();
        $r = new ReflectionObject($this);
        foreach($r->getProperties() as $property) {
            if ($property->getName() != '_mapper') {
                $properties[] = $property->getName();
            }
        }

        //Возвращаем список своиств объекта для сериализации
        return $properties;
    }

    /**
     * Метод установки параметров модели
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        if (is_array($options) && count($options) > 0) {
            foreach($options as $name => $value) {
                $this->__set($name, $value);
            }
        }
    }

    /**
     * Магический метод __get
     *
     * @param string $name
     *
     * @throws Exception
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            throw new Exception('Unknown method ' . $method . ' called in ' . get_class($this));
        }
    }

    /**
     * Магический метод __set
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            $this->$method($value);
        }
    }

    /**
     * Получение ключа записи в хранилище
     *
     * @return string
     */
    public function getKey()
    {
        return self::KEY_PREFIX . md5($this->getUserSid() . $this->getGameSid());
    }

    /**
     * Установка идентификатора сессии пользователя, которого необходимо оповещать
     *
     * @param string $sid
     */
    public function setUserSid($sid)
    {
        $this->_userSid = $sid;
    }

    /**
     * Получение идентификатора сессии пользователя, которого необходимо оповещать
     *
     * @return string
     */
    public function getUserSid()
    {
        return $this->_userSid;
    }

    /**
     * Установка идентификатора сессии приватного игрового стола, который был создан пользователем
     *
     * @param string $sid
     */
    public function setGameSid($sid)
    {
        $this->_gameSid = $sid;
    }

    /**
     * Получение идентфиикатора сессии приватного игрового стола, который был создан пользователем
     *
     * @return string
     */
    public function getGameSid()
    {
        return $this->_gameSid;
    }

    /**
     * Установка списка пользователей, принявших приглашение
     *
     * @param array $confirmUsers
     */
    public function setConfirm(array $confirmUsers)
    {
        $this->_confirm = $confirmUsers;
    }

    /**
     * Добавление идентификатора сессии пользователя, принявшего приглашение
     *
     * @param string $sid
     * @return bool
     */
    public function addConfirmUser($sid)
    {
        //Удаление из списка отказавшихся (в случае отказа, а потом заход в игру через зал)
        $this->delDeclineUser($sid);
        //Добавление в список согласившихся
        if (!in_array($sid, $this->_confirm)) {
            $this->_confirm[] = $sid;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Удаление идентификатора сессии пользователя из списка принявших приглашение
     *
     * @param string $sid
     * @return bool
     */
    public function delConfirmUser($sid)
    {
        $index = array_search($sid, $this->_confirm);
        if (false !== $index) {
            unset($this->_confirm[$index]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получение списка пользователей, принявших приглашение
     *
     * @return array
     */
    public function getConfirm()
    {
        return $this->_confirm;
    }

    /**
     * Установка списка пользователей, отказавшихся от приглашения
     *
     * @param array $declineUsers
     */
    public function setDecline(array $declineUsers)
    {
        $this->_decline = $declineUsers;
    }

    /**
     * Добавление идентификатора пользователя, откозавшегося от приглашения
     *
     * @param string $sid
     * @return bool
     */
    public function addDeclineUser($sid)
    {
        //Удачение пользователя из списка согласившихся (в случае согласия, а потом выхода из игры)
        $this->delConfirmUser($sid);
        //Добавление в список отказавшихся
        if (!in_array($sid, $this->_decline)) {
            $this->_decline[] = $sid;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Удаление идентификатора пользователя из списка отказавшихся от приглашения
     *
     * @param string $sid
     * @return bool
     */
    public function delDeclineUser($sid)
    {
        $index = array_search($sid, $this->_decline);
        if (false !== $index) {
            unset($this->_decline[$index]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получение списка пользователей, отказавшихся от приглашения
     *
     * @return array
     */
    public function getDecline()
    {
        return $this->_decline;
    }

    /**
     * Установка флага закрытия окна оповещения
     *
     * @param bool $closed
     */
    public function setClosed($closed)
    {
        $this->_closed = $closed;
    }

    /**
     * Получение флага закрытия окна оповещения
     *
     * @return bool
     */
    public function getClosed()
    {
        return $this->_closed;
    }

    /**
     * Проверка необходимости закрытия окна оповещения
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->_closed;
    }

    /**
     * Установка флага оповещения пользователя
     *
     * @param bool $notified
     */
    public function setNotified($notified)
    {
        $this->_notified = $notified;
    }

    /**
     * Получение флага оповещения пользователя
     *
     * @return bool
     */
    public function getNotified()
    {
        return $this->_notified;
    }

    /**
     * Проверка уведомления пользователя
     *
     * @return bool
     */
    public function isNotify()
    {
        return $this->_notified;
    }

    /**
     * Сброс флага уведомления пользователя
     *
     * @return void
     */
    public function resetNotify()
    {
        $lock = 0;
        if (!$this->isLock(posix_getpid())) {
            $this->lock();
            $lock = 1;
        }
        $this->setNotified(false);
        if ($lock) {
            $this->saveAndUnlock();
        }
    }

    /**
     * Установка объекта модели доступа к данным хранилищ
     *
     * @param App_Model_Mapper_Interface $mapper
     * @return App_Model_Room_Notification_InviteGame
     */
    public function setMapper(App_Model_Mapper_Interface $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Получение объекта модели доступа к данным хранилищ
     *
     * @return App_Model_Mapper_Storage
     */
    public function getMapper()
    {
        if (null === $this->_mapper) {
            $this->setMapper(new App_Model_Mapper_Storage());
        }

        return $this->_mapper;
    }

    /**
     * Поиск данных модели
     *
     * @param string $key
     * @return bool
     */
    public function find($key)
    {
        return $this->getMapper()->find($key, $this);
    }

    /**
     * Поиск записи в хранилище по установленным данным объекта модели
     *
     * @return bool
     */
    public function findByData()
    {
        return $this->getMapper()->find($this->getKey(), $this);
    }

    /**
     * Сохранение данных модели
     *
     * @return bool
     */
    public function save()
    {
        //Сохранение данных уведомления
        if (!$this->getMapper()->save($this)) {
            return false;
        }

        //Запись ключа уведомления в сессии пользователя
        $userSession = new App_Model_Session_User();
        if (!$userSession->find($this->getUserSid())) {
            return false;
        }
        $userSession->lock();
        $userSession->addNotification($this->getKey());
        $result = $userSession->save();
        $userSession->unlock();

        //Возвращаем результат
        return $result;
    }

    /**
     * Удаление данных модели
     *
     * @return bool
     */
    public function delete()
    {
        //Удаление ключа записи уведомления из сессии пользователя
        $userSession = new App_Model_Session_User(array(
            'sid' => $this->getUserSid()
        ));
        $userSession->lock();
        $userSession->find($this->getUserSid());
        $userSession->delNotification($this->getKey());
        $userSession->save();
        $userSession->unlock();

        //Удаление уведомления
        return $this->getMapper()->delete($this);
    }

    /**
     * Блокировка данных модели
     *
     */
    public function lock()
    {
        $this->getMapper()->lock($this);
    }

    /**
     * Блокировка и получение актуальных данных записи
     *
     * @return void
     */
    public function lockAndUpdate()
    {
        $this->lock();
        $this->find($this->getKey());
    }

    /**
     * Разблокировка данных модели
     *
     */
    public function unlock()
    {
        $this->getMapper()->unlock($this);
    }

    /**
     * Сохранение и разблокировка данных
     *
     * @return void
     */
    public function saveAndUnlock()
    {
        $this->save();
        $this->unlock();
    }

    /**
     * Проверка блокировки данных модели
     * В случае передачи параметра pid, проверяется блокировка данных модели указанным процессом
     *
     * @param string|null $pid
     * @return bool
     */
    public function isLock($pid = null)
    {
        return $this->getMapper()->isLock($this, $pid);
    }

    /**
     * Получение модели в виде строки
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getKey();
    }

    /**
     * Клиент
     *
     * @return string
     */
    public function client()
    {
        return $this->getUserSid();
    }

    /**
     * Выполнение оповещения
     *
     * @return string
     */
    public function notify()
    {
        //Проверка необходимости оповещения
        if ($this->isNotify()) {
            //Пользователь уже уведомлен
            return false;
        }

        //Формирование тела оповещения
        $body = $this->_saveXml();
        //Проверка необходимости удаления оповещения
        if ($this->isClosed()) {
            //Удаление оповещения
            $this->delete();
        } else {
            //Установка флага уведомления пользователя
            $this->lock();
            $this->setNotified(true);
            $this->save();
            $this->unlock();
        }

        //Возвращаем тело оповещения
        return $body;
    }

    /**
     * Проверка истечения срока оповещения
     *
     * @return bool
     */
    public function isExpired()
    {
        //Безсрочное оповещение
        return false;
    }

    /**
     * Флаг передачи одного оповщения за раз
     *
     * @return bool
     */
    public function isSingle()
    {
        return true;
    }

    /**
     * Уничтожение оповещение (уведомление о закрытии)
     *
     * @return void
     */
    public function destroy()
    {
        $this->setClosed(true);
        //Сбрасываем флаг уведомления
        $this->resetNotify();
    }

    /**
     * Формирование тела оповещения
     *
     * @return mixed
     */
    private function _saveXml()
    {
        //Проверка наличия согласившихся либо отказавшихся пользователей
        if (!$this->isClosed() && !count($this->getConfirm()) && !count($this->getDecline())) {
            //Возвращаем пустое оповещение
            return '';
        }

        //XML генератор
        $xml = new XMLWriter();
        $xml->openMemory();

        //Заголовок оповещения
        $xml->startElement('message');
        $xml->writeAttribute('name', 'inviteInfo');
        if ($this->isClosed()) {
            //Возвращаем только заголовок с атрибутом закрытия
            $xml->writeAttribute('close', 1);
            $xml->endElement();
            $xml->flush(false);
        } else {
            $xml->writeAttribute('close', 0);
        }

        //Данные согласившихся пользователей
        $xml->startElement('confirm');
        foreach($this->getConfirm() as $userSid) {
            //Получение данных сессии пользователя
            $session = new App_Model_Session_User();
            if ($session->find($userSid)) {
                $xml->startElement('user');
                $xml->writeAttribute('sid', $session->getSid());
                $xml->writeElement('name', $session->getSocialUser()->getName());
                $xml->writeElement('photo', $session->getSocialUser()->getPhotoUrl());
                $xml->endElement();
            }
        }
        $xml->endElement();

        //Данные отказавшихся пользователей
        $xml->startElement('decline');
        foreach($this->getDecline() as $userSid) {
            //Получение данных сессии пользователя
            $session = new App_Model_Session_User();
            if ($session->find($userSid)) {
                $xml->startElement('user');
                $xml->writeAttribute('sid', $session->getSid());
                $xml->writeElement('name', $session->getSocialUser()->getName());
                $xml->writeElement('photo', $session->getSocialUser()->getPhotoUrl());
                $xml->endElement();
            }
        }
        $xml->endElement();

        //Закрытие блока оповещения
        $xml->endElement();

        //Отдаем тело оповещения
        return $xml->flush(false);
    }
}
