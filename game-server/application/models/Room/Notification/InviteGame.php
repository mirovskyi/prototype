<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 17.03.12
 * Time: 15:53
 *
 *
 */
class App_Model_Room_Notification_InviteGame implements App_Model_Interface, Core_Protocol_NotificationInterface
{

    /**
     * Префикс ключа записи
     */
    const KEY_PREFIX = 'ign:';

    /**
     * Время жизни оповещения (сек.)
     */
    const LIFETIME = 30;

    /**
     * Идентификатор сессии пользователя
     *
     * @var string
     */
    protected $_userSid;

    /**
     * Идентификатор сессии игры
     *
     * @var string
     */
    protected $_gameSid;

    /**
     * Идентификатор сессии пользователя, создавшего игровой стол
     *
     * @var string
     */
    protected $_creatorSid;

    /**
     * Время создания оповещения
     *
     * @var int
     */
    protected $_time;

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
        //Установка времени создания оповещения
        $this->_time = time();
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
     * Установка идентификатора сессии пользователя
     *
     * @param string $sid
     * @return App_Model_Room_Notification_InviteGame
     */
    public function setUserSid($sid)
    {
        $this->_userSid = $sid;
        return $this;
    }

    /**
     * Получение идентификатора сессии пользователя
     *
     * @return string
     */
    public function getUserSid()
    {
        return $this->_userSid;
    }

    /**
     * Установка идентификатора сессии игры
     *
     * @param string $sid
     * @return App_Model_Room_Notification_InviteGame
     */
    public function setGameSid($sid)
    {
        $this->_gameSid = $sid;
        return $this;
    }

    /**
     * Получение идентификатора сессии игры
     *
     * @return string
     */
    public function getGameSid()
    {
        return $this->_gameSid;
    }

    /**
     * Установка идентификатора сессии создателя игрового стола
     *
     * @param string $sid
     * @return App_Model_Room_Notification_InviteGame
     */
    public function setCreatorSid($sid)
    {
        $this->_creatorSid = $sid;
        return $this;
    }

    /**
     * Получение идентификатора сессии создателя игрового стола
     *
     * @return string
     */
    public function getCreatorSid()
    {
        return $this->_creatorSid;
    }

    /**
     * Установка времени содания оповещения
     *
     * @param int $time
     * @return App_Model_Room_Notification_InviteGame
     */
    public function setTime($time)
    {
        $this->_time = $time;
        return $this;
    }

    /**
     * Получение времени созданяи оповещения
     *
     * @return int
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     * Получение количества секунд до истечения срока действия оповещения
     *
     * @return int
     */
    public function getRestTime()
    {
        $restTime = $this->getTime() - time();
        if ($restTime < 0) {
            $restTime = 0;
        }

        return $restTime;
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
     * Проверка необходимости закрытии окна оповещения
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->_closed;
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
        $userSession->saveAndUnlock();

        //Удаляем пользователя из списка приглашенных в игру
        $gameSession = new App_Model_Session_Game(array(
            'sid' => $this->getGameSid()
        ));
        $gameSession->lock();
        $gameSession->find($this->getGameSid());
        $gameSession->delInvite($this->getUserSid());
        $gameSession->saveAndUnlock();


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
     * Разблокировка данных модели
     *
     */
    public function unlock()
    {
        $this->getMapper()->unlock($this);
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
     * Клиент, которого необходимо оповестить
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
        //Формирование тела оповещения
        $body = $this->_saveXml();
        //Если срок оповещения истек, уведомляем создателя игры об отказе приглашения
        if ($this->isExpired()) {
            //Поиск оповещения создателя об принятии/отказе приглашения
            $inviteInfoNotification = new App_Model_Room_Notification_InviteInfo();
            $inviteInfoNotification->setUserSid($this->getCreatorSid());
            $inviteInfoNotification->setGameSid($this->getGameSid());
            if ($inviteInfoNotification->findByData()) {
                //Добавление отказа пользователя
                $inviteInfoNotification->lockAndUpdate();
                if ($inviteInfoNotification->addDeclineUser($this->getUserSid())) {
                    //Сбрасываем флаг уведомления создателя игры (для обновления оповещения)
                    $inviteInfoNotification->resetNotify();
                }
                $inviteInfoNotification->saveAndUnlock();
            }
        }
        //Проверка необходимости удаления оповещения
        if ($this->isExpired() || $this->isClosed()) {
            //Удаляем оповещение
            $this->delete();
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
        return time() > ($this->_time + self::LIFETIME);
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
        //Установка флага закрытия окна оповещения
        $this->setClosed(true);
        //Сохранение изменений
        $this->save();
    }

    /**
     * Формирование тела оповещения пользователя о приглашении в игру
     *
     * @return bool|string
     */
    private function _saveXml()
    {
        //Получаем данные пригласившего пользователя
        $creatorSession = new App_Model_Session_User();
        if (!$creatorSession->find($this->getCreatorSid())) {
            return false;
        }

        //Получаем данные сессии игровы
        $gameSession = new App_Model_Session_Game();
        if (!$gameSession->find($this->getGameSid())) {
            return false;
        }

        //Формирование XML оповещения
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();

        $xmlWriter->startElement('message');
        $xmlWriter->writeAttribute('name', 'invite');
        $xmlWriter->writeAttribute('time', $this->getRestTime());

        //Флаг закрытия окна оповещения
        if ($this->isExpired() || $this->isClosed()) {
            //Возвращаем только заголовок с атрибутом закрытия
            $xmlWriter->writeAttribute('close', 1);
            $xmlWriter->endElement();
            return $xmlWriter->flush(false);
        } else {
            $xmlWriter->writeAttribute('close', 0);
        }

        //Данные игры
        $xmlWriter->startElement('game');
        $xmlWriter->writeAttribute('sid', $this->getGameSid());
        $xmlWriter->writeElement('bet', $gameSession->getData()->getBet());
        $xmlWriter->writeElement('mb', $gameSession->getMinBalance());
        $xmlWriter->writeElement('me', $gameSession->getMinExperience());
        $xmlWriter->writeElement('o', $gameSession->getSpectator());
        $xmlWriter->endElement();

        //Данные пригласившего пользователя
        $xmlWriter->startElement('creator');
        $xmlWriter->writeAttribute('sid', $this->getCreatorSid());
        $xmlWriter->writeElement('id', $creatorSession->getSocialUser()->getId());
        $xmlWriter->writeElement('name', $creatorSession->getSocialUser()->getName());
        $xmlWriter->writeElement('photo', $creatorSession->getSocialUser()->getPhotoUrl());
        $xmlWriter->endElement();

        $xmlWriter->endElement();

        //Возвращаем тело оповещения
        return $xmlWriter->flush(false);
    }
}