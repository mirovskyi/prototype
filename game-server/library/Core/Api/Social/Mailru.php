<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 06.11.12
 * Time: 15:20
 *
 * Класс для работы с API соц. сети Mail.RU
 */
class Core_Api_Social_Mailru
{

    /**
     * Флаг безопасности запросов (сервер-сервер)
     */
    const SECURE = 1;

    /**
     * URL адрес API сервера
     *
     * @var string
     */
    protected $_apiServer;

    /**
     * Идентификатор приложения
     *
     * @var string
     */
    protected $_appId;

    /**
     * Ключ сессии текущего пользователя
     *
     * @var string
     */
    protected $_sessionKey;

    /**
     * Секретный ключ для подписи безопасных запросов сервер-сервер
     *
     * @var string
     */
    protected $_secretKey;

    /**
     * Код ошибки
     *
     * @var int
     */
    protected $_errorNo;

    /**
     * Сообщение об ошибке
     *
     * @var string
     */
    protected $_errorMsg;


    /**
     * Создание нового объекта
     *
     * @param string      $apiServer  Адрсе API сервера
     * @param string      $addId      Идентификатор приложения
     * @param string|null $sessionKey Ключ сессии пользователя
     * @param string|null $secretKey  Секретный ключ
     */
    public function __construct($apiServer, $addId, $sessionKey = null, $secretKey = null)
    {
        //Установка параметров
        $this->setApiServer($apiServer);
        $this->setAppId($addId);
        $this->setSessionKey($sessionKey);
        $this->setSecretKey($secretKey);
    }

    /**
     * Установка URL адреса API сервера
     *
     * @param string $apiServer
     */
    public function setApiServer($apiServer)
    {
        $this->_apiServer = $apiServer;
    }

    /**
     * Получение URL адреса API сервера
     *
     * @return string
     */
    public function getApiServer()
    {
        return $this->_apiServer;
    }

    /**
     * Установка идентификатора приложения
     *
     * @param string $appId
     */
    public function setAppId($appId)
    {
        $this->_appId = $appId;
    }

    /**
     * Получение идентификатора приложения
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->_appId;
    }

    /**
     * Установка ключа сессии пользователя
     *
     * @param string $sessionKey
     */
    public function setSessionKey($sessionKey)
    {
        $this->_sessionKey = $sessionKey;
    }

    /**
     * Получение ключа сессии пользователя
     *
     * @return string
     */
    public function getSessionKey()
    {
        return $this->_sessionKey;
    }

    /**
     * Установка секретного ключа для подписи безопасных запросов (сервер-сервер)
     *
     * @param string $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->_secretKey = $secretKey;
    }

    /**
     * Получение секретного ключа для подписи безопасных запросов (сервер-сервер)
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->_secretKey;
    }

    /**
     * Установка кода ошибки
     *
     * @param int $errorNo
     */
    public function setErrorNo($errorNo)
    {
        $this->_errorNo = $errorNo;
    }

    /**
     * Получение кода ошибки
     *
     * @return int
     */
    public function getErrorNo()
    {
        return $this->_errorNo;
    }

    /**
     * Установка сообщения об ошибке
     *
     * @param string $errorMsg
     */
    public function setErrorMsg($errorMsg)
    {
        $this->_errorMsg = $errorMsg;
    }

    /**
     * Получение сообщения об ошибке
     *
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->_errorMsg;
    }

    /**
     * Получение информации о пользователе(ях)
     *
     * @param string|array $uids Идентификатор(ы) пользователя(ей)
     * @return array
     */
    public function getUserInfo($uids)
    {
        //Преобразование массива идентификаторрв в строку
        if (is_array($uids)) {
            $uids = implode(',', $uids);
        }

        //Формирование параметров запроса
        $params = array('uids' => $uids);

        //Выполнение запроса
        return $this->_sendRequest('users.getInfo', $params);
    }

    /**
     * Формирование строки параметров запроса
     *
     * @param array $params Массив параметров запроса
     *
     * @return string
     */
    protected function _buildHttpRequest(array $params)
    {
        //Формирование строки запроса
        $httpRequest = array();
        foreach($params as $key => $val) {
            $httpRequest[] = $key . '=' . $val;
        }

        //Возвращаем строку параметров запроса
        return implode('&', $httpRequest);
    }

    /**
     * Формирование подписи запроса
     *
     * @param array $requestParams Параметры запроса
     * @return string
     */
    protected function _signature(array $requestParams)
    {
        //Сортировка параметров запроса в алфавитном порядке
        ksort($requestParams, SORT_STRING);
        //Формирование строки хэща
        $strHash = str_replace('&', '', $this->_buildHttpRequest($requestParams));
        //Добавление секретного ключа
        $strHash .= $this->getSecretKey();
        //Получение md5 хэша
        return md5($strHash);
    }

    /**
     * Выполнение запроса
     *
     * @param string $method Наименование метода
     * @param array  $params Параметры запроса
     * @return array
     */
    protected function _sendRequest($method, array $params)
    {
        //Добавление обязательных параметров запроса
        $params['method'] = $method;
        $params['app_id'] = $this->getAppId();
        $params['secure'] = self::SECURE;
        //Формирование подписи запроса
        $sig = $this->_signature($params);
        //Добавление подписи в запрос
        $params['sig'] = $sig;

        //Создание соединения
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getApiServer());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_buildHttpRequest($params));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //Выполнение запроса
        $response = curl_exec($ch);
        //Проверка наличия ошибки
        if (curl_errno($ch)) {
            //Установка ошибки
            $this->setErrorNo(curl_errno($ch));
            $this->setErrorMsg(curl_error($ch));
        }
        //Закрываем соединение
        curl_close($ch);

        //Проверка наличия ответа
        if (!$response) {
            $this->setErrorNo(1001);
            $this->setErrorMsg('Empty response');
            return $response;
        }
        //Преобразование ответа в массив
        $response = json_decode($response, true);
        //Проверка наличия ошибки в ответе
        if (isset($response['error'])) {
            //Данные ошибки
            $error = $response['error'];
            //Записываем данные ошибки
            $this->setErrorNo($error['error_code']);
            $this->setErrorMsg($error['error_msg']);
        }

        //Возвращаем результат запроса
        return $response;
    }

}
