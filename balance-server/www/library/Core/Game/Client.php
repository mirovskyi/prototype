<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 18.05.12
 * Time: 16:02
 *
 * Клинт игрового сервиса
 */
class Core_Game_Client
{

    /**
     * Кодировка
     *
     * @var string
     */
    protected $_encoding;

    /**
     * Адрес игрового сервера
     *
     * @var string
     */
    protected $_url;

    /**
     * Пространство имен обработчика
     *
     * @var string
     */
    protected $_namespace;

    /**
     * Последний запрос к игровому серверу
     *
     * @var string
     */
    protected $_lastRequest;

    /**
     * Последний ответ игрового сервера
     *
     * @var string
     */
    protected $_lastResponse;


    /**
     * Создание нового объекта клиента
     *
     * @param string $url URL игрового сервера
     * @param string $namespace Пространство имен сервиса
     * @param string $encoding  Кодировка
     */
    public function __construct($url, $namespace = 'api', $encoding = 'UTF-8')
    {
        $this->setUrl($url)
             ->setNamespace($namespace)
             ->setEncoding($encoding);
    }

    /**
     * Установка URL игрового сервера
     *
     * @param string $url
     * @return Core_Game_Client
     */
    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    /**
     * Получение URL игрового сервера
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Установка пространства имен сервиса
     *
     * @param string $namespace
     * @return Core_Game_Client
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
        return $this;
    }

    /**
     * Получение пространства имен сервиса
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Установка кодировки
     *
     * @param string $encoding
     * @return Core_Game_Client
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Получение кодировки
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Вызов метода удаленного игрового сервиса
     *
     * @param string $method
     * @param array $arguments
     * @return SimpleXMLElement
     */
    public function __call($method, $arguments)
    {
        //Формирование имени метода
        $methodName = $this->getNamespace() . '.' . $method;
        //Получение параметров запроса
        $params = array();
        if (isset($arguments[0]) && is_array($arguments[0])) {
            $params = $arguments[0];
        }

        //Формирование тела запроса
        $this->_lastRequest = $this->_toXml($methodName, $params);

        //Выполнение запроса
        $response = $this->_sendRequest($this->getUrl(), $this->_lastRequest);

        //Возвращаем объекта SimpleXMLElement ответа
        return new SimpleXMLElement($response);
    }

    /**
     * Получение последнего запроса
     *
     * @return string
     */
    public function __lastRequest()
    {
        return $this->_lastRequest;
    }

    /**
     * Получение последнего ответа игрового сервиса
     *
     * @return string
     */
    public function __lastResponse()
    {
        return $this->_lastResponse;
    }

    /**
     * Получение тела запроса в виде XML
     *
     * @param string $method Имя метода
     * @param array $params Параметры запроса
     * @return string
     */
    private function _toXml($method, array $params)
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', $this->getEncoding());
        $xml->startElement('request');
        $xml->startElement('methodCall');
        $xml->writeAttribute('name', $method);
        //Параметры запроса
        if (count($params)) {
            $xml->startElement('params');
            foreach($params as $key => $value) {
                $xml->writeElement($key, $value);
            }
            $xml->endElement();
        }
        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();

        return $xml->flush(false);
    }

    /**
     * Выполнение запроса на игровой сервис
     *
     * @param string $url
     * @param string $requestBody
     * @return string
     * @throws Exception
     */
    private function _sendRequest($url, $requestBody)
    {
        //Формирование запроса
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //Выполнение запроса
        $response = curl_exec($ch);
        //Установка последнего ответа
        $this->_lastRequest = $response;

        //Получение данных об ошибке
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        //Закрываем соединение
        curl_close($ch);

        //Проверка наличия ошибок
        if ($errno) {
            throw new Exception($error, $errno);
        }

        //Возаращаем ответ
        return $response;
    }

}
