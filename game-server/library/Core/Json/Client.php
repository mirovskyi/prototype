<?php


/**
 * Description of Client
 *
 * @author aleksey
 */
class Core_Json_Client 
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
     * @var boolean 
     */
    protected $_debug = false;
    
    /**
     * Объект логирования
     *
     * @var Core_Log 
     */
    protected $_log = null;
    
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
     * @param string $url
     * @param string $namespace
     * @param boolean $debug
     * @param Core_Log|null $log
     * @internal param string $namepace
     */
    public function __construct($url = null, $namespace = '', $debug = false, Core_Log $log = null)
    {
        $this->setUrl($url)
             ->setNamespace($namespace)
             ->setDebug($debug);
        if (null !== $log) {
            $this->setLog($log);
        }
    }
    
    /**
     * Установка адреса JSON RPC сервера
     *
     * @param string $url
     * @return Core_Json_Client 
     */
    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
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
     * @return Core_Json_Client 
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
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
     * @return Core_Json_Client 
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
        return $this;
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
     * @param boolean $debug
     * @return Core_Json_Client 
     */
    public function setDebug($debug = true)
    {
        $this->_debug = $debug;
        return $this;
    }
    
    /**
     * Проверка дэбага
     *
     * @return boolean 
     */
    public function isDebug()
    {
        return $this->_debug;
    }
    
    /**
     * Установка объекта логирования
     *
     * @param Core_Log $log
     * @return Core_Json_Client 
     */
    public function setLog(Core_Log $log)
    {
        $this->_log = $log;
        return $this;
    }
    
    /**
     * Получение объекта логирования
     *
     * @return Core_Log 
     */
    public function getLog()
    {
        return $this->_log;
    }
    
    /**
     * Установка тела последнего запроса
     *
     * @param string $request
     * @return Core_Json_Client 
     */
    public function setLastRequest($request)
    {
        $this->_lastRequest = $request;
        return $this;
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
     * @return Core_Json_Client 
     */
    public function setLastResponse($response)
    {
        $this->_lastResponse = $response;
        return $this;
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
     * @throws Zend_Exception
     * @throws Core_Api_Exception
     * @return mixed
     */
    public function __call($name, $arguments) 
    {
        //Проверка имени метода
        if (!is_scalar($name)) {
            throw new Zend_Exception('Method name has no scalar value');
        }
        
        //Проверка параметров метода
        if (is_array($arguments)) {
            $arguments = array_values($arguments);
        } else {
            throw new Zend_Exception('Params must be given as array');
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
            throw new Zend_Exception('Unable to connect to ' . $this->getUrl());
        }
        
        //Вывод дэбага
        if ($this->isDebug()) {
            $this->_debug($debug);
        }
        
        //Проверка ответа
        if ($response['id'] != $this->getId()) {
            throw new Zend_Exception('Incorrect response id (request id: ' 
                                     . $this->getId() . ', response id: '
                                     . $response['id'] . ')');
        }
        if (isset($response['error']) && !is_null($response['error'])) {
            throw new Core_Api_Exception($response['error']['message'], $response['error']['code']);
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
        if ($this->getLog() instanceof Core_Log) {
            $this->getLog()->info($message);
        } else {
            echo nl2br($message);
        }
    }
}