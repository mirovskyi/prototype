<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 03.10.12
 * Time: 18:53
 *
 * Клиент JSON-RPC
 */
class JsonClient
{

    /**
     * Разделитель протранства имен м имени метода
     */
    const NAMESPACE_SEPARATOR = '.';

    /**
     * Идентификатор запроса
     *
     * @var integer
     */
    protected $_id = null;

    /**
     * Пространство имен
     *
     * @var string
     */
    protected $_namespace;

    /**
     * URL JSON RPC сервера
     *
     * @var string
     */
    protected $_url;

    /**
     * Флаг дэбага
     *
     * @var string|boolean
     */
    protected $_debug = false;

    /**
     * Тело последнего запроса
     *
     * @var string
     */
    protected $_lastRequest;

    /**
     * Тело последнего ответа
     *
     * @var string
     */
    protected $_lastResponse;


    /**
     * __construct
     *
     * @param string  $url       Адрес сервера
     * @param string  $namespace Пространство имен запросов
     * @param boolean $debug     Наличие дэбага (путь к директории логов либо FALSE)
     */
    public function __construct($url = null, $namespace = '', $debug = false)
    {
        $this->setUrl($url);
        $this->setNamespace($namespace);
        $this->setDebug($debug);
    }

    /**
     * Установка адреса JSON RPC сервера
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->_url = $url;
    }

    /**
     * Получение адреса JSON RPC сервера
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Установка идентификатора запроса
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Получение идентификатора запроса
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Установка пространства имен
     *
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
    }

    /**
     * Получение пространства имен
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Установка дэбага
     *
     * @param string|boolean $debug
     */
    public function setDebug($debug)
    {
        $this->_debug = $debug;
    }

    /**
     * Получение директории логов
     *
     * @return string|bool
     */
    public function getDebug()
    {
        return $this->_debug;
    }

    /**
     * Проверка дэбага
     *
     * @return boolean
     */
    public function isDebug()
    {
        return (bool)$this->_debug;
    }

    /**
     * Установка тела последнего запроса
     *
     * @param string $request
     */
    public function setLastRequest($request)
    {
        $this->_lastRequest = $request;
    }

    /**
     * Получение тела последнего запроса
     *
     * @return string
     */
    public function getLastRequest()
    {
        return $this->_lastRequest;
    }

    /**
     * Установка тела последнего ответа
     *
     * @param string $response
     */
    public function setLastResponse($response)
    {
        $this->_lastResponse = $response;
    }

    /**
     * Получение тела последнего ответа
     *
     * @return string
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }

    /**
     * Вызов удаленного метода
     *
     * @param string $name Имя метода
     * @param array $arguments Параметры вызова
     *
     * @throws Exception
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        //Проверка имени метода
        if (!is_scalar($name)) {
            throw new Exception('Method name has no scalar value');
        }

        //Проверка параметров метода
        if (is_array($arguments)) {
            $arguments = array_values($arguments);
        } else {
            throw new Exception('Params must be given as array');
        }

        //Формирование имени метода
        if ($this->getNamespace()) {
            $method = $this->getNamespace() . self::NAMESPACE_SEPARATOR . $name;
        } else {
            $method = $name;
        }

        //Подготовка запроса
        $request = array(
            'method' => $method,
            'params' => $arguments,
            'id' => $this->getId()
        );

        //Кодирование в JSON формат
        $this->setLastRequest(json_encode($request));
        //Дэбажим
        $debug = ' ***** Request to ' . $this->getUrl() . '*****'
                 . PHP_EOL . $this->getLastRequest() . PHP_EOL
                 . ' ***** End Request *****';

        //Открываем соединение
        if (($fp = $this->_openStream())) {
            //Чтение ответа
            $response = '';
            while($row = fgets($fp)) {
                $response .= trim($row) . PHP_EOL;
            }
            $this->setLastResponse($response);
            //Дэбажим
            $debug .= PHP_EOL . ' ***** Response from ' . $this->getUrl() . '*****'
                              . PHP_EOL . $this->getLastResponse()
                              . ' ***** End Response *****' . PHP_EOL;
            //Преобразование JSON стоки ответа в массив
            $response = json_decode($response, true);
        } else {
            throw new Exception('Unable to connect to ' . $this->getUrl());
        }

        //Вывод дэбага
        if ($this->isDebug()) {
            $this->_debug($debug);
        }

        //Проверка ответа
        if ($response['id'] != $this->getId()) {
            throw new Exception('Incorrect response id (request id: '
                                . $this->getId() . ', response id: '
                                . $response['id'] . ')');
        }
        if (isset($response['error']) && !is_null($response['error'])) {
            throw new Exception($response['error']['message'], $response['error']['code']);
        }

        //Возвращаем результат ответа
        return $response['result'];
    }

    /**
     * Открытие соединения с JSON RPC сервером
     *
     * @return resource
     */
    protected function _openStream()
    {
        $opts = array('http' => array (
            'method'  => 'POST',
            'header'  => 'Content-type: application/json',
            'content' => $this->getLastRequest()
        ));
        $context  = stream_context_create($opts);

        return fopen($this->getUrl(), 'r', false, $context);
    }

    /**
     * Дэбаг
     *
     * @param string $message
     */
    protected function _debug($message)
    {
        //Путь к файлу лога
        $filename = $this->getDebug() . PATH_SEPARATOR . date('Y-m-d') . '.log';
        $filename = str_replace(PATH_SEPARATOR . PATH_SEPARATOR, PATH_SEPARATOR, $filename);

        //Запись лога
        @file_put_contents($filename, $message . PHP_EOL, FILE_APPEND);
    }
}
