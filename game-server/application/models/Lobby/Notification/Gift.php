<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 04.10.12
 * Time: 12:07
 *
 * Класс оповещения игрока о падарке
 */
class App_Model_Lobby_Notification_Gift implements Core_Protocol_NotificationInterface
{


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
     * __construct
     *
     * @param array $options
     */
    public function __construct($options = null)
    {
        $this->setOptions($options);
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
     * Клиент
     *
     * @return string
     */
    public function client()
    {
        return '';
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


}
