<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 03.10.12
 * Time: 10:57
 *
 * Класс оповещения о подарке пользователю
 */
class App_Model_Room_Notification_Gift implements Core_Protocol_NotificationInterface, App_Model_Interface
{

    /**
     * Префикс ключа записи
     */
    const KEY_PREFIX = 'gn:';

    /**
     * Идентификатор сессии пользователя, для которого необходимо оповещение
     *
     * @var string
     */
    protected $_userSid;

    /**
     * Наименование подарка
     *
     * @var string
     */
    protected $_name;

    /**
     * Имя пользователя, подарившего подарок
     *
     * @var string
     */
    protected $_fromUserName;

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
     * Установка идентификатора сессии пользователя, для которого предназначено оповещение
     *
     * @param string $sid
     */
    public function setUserSid($sid)
    {
        $this->_userSid = $sid;
    }

    /**
     * Получение идентификатора сессии пользователя, для которого преднозначено оповещение
     *
     * @return string
     */
    public function getUserSid()
    {
        return $this->_userSid;
    }

    /**
     * Установка наименования подарка
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Получение наименования подарка
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Установка имени пользователя, подарившего подарок
     *
     * @param string $name
     */
    public function setFromUserName($name)
    {
        $this->_fromUserName = $name;
    }

    /**
     * Получение имени пользователя, подарившего подарок
     *
     * @return string
     */
    public function getFromUserName()
    {
        return $this->_fromUserName;
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
        return $this->getMapper()->find($this->_getKey(), $this);
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
        $userSession->addNotification($this->_getKey());
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
        $userSession->delNotification($this->_getKey());
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
        $this->find($this->_getKey());
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
        return $this->_getKey();
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
        //Формирование тела оповещения
        $body = $this->_saveXml();
        //Удаление оповещения (показываетя один раз)
        $this->delete();

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
        return false;
    }

    /**
     * Уничтожение оповещения
     *
     * @return void
     */
    public function destroy()
    {
        $this->delete();
    }

    /**
     * Формирование тела оповещения
     *
     * @return string
     */
    private function _saveXml()
    {
        //XML генератор
        $xml = new XMLWriter();
        $xml->openMemory();

        //Заголовок оповещения
        $xml->startElement('message');
        $xml->writeAttribute('name', 'gift');

        //Данные подарка
        $xml->startElement('gift');
        $xml->writeAttribute('name', $this->getName());
        $xml->writeAttribute('from', $this->getFromUserName());
        $xml->endElement();

        //Закрытие блока оповещения
        $xml->endElement();

        //Отдаем тело оповещения
        return $xml->flush(false);
    }

    /**
     * Получение ключа записи в хранилище
     *
     * @return string
     */
    private function _getKey()
    {
        return self::KEY_PREFIX . md5($this->getUserSid() . $this->getName() . $this->getFromUserName());
    }
}
