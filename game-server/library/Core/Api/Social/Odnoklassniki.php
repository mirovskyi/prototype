<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 20.11.12
 * Time: 10:45
 *
 * Класс работы с API социальной сети Odnoklassniki
 */
class Core_Api_Social_Odnoklassniki
{

    /**
     * Формат ответа API сервера
     */
    const FORMAT = 'JSON';

    /**
     * URI запроса к REST API
     */
    const URI = 'fb.do';

    /**
     * Ключ приложения
     *
     * @var string
     */
    protected $_applicationKey;

    /**
     * Адрес сервера API
     *
     * @var string
     */
    protected $_apiServer;

    /**
     * Ключ сессии текущего пользователя
     *
     * @var string
     */
    protected $_sessionKey;

    /**
     * Секретный ключ сессии, для подписи запросов
     *
     * @var string
     */
    protected $_secretKey;

    /**
     * Идентификатор запроса
     *
     * @var string
     */
    protected $_callId;

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
     * @param string      $apiServer      Адрес API сервера
     * @param string      $applicationKey Ключ приложения
     * @param string|null $sessionKey     Ключ сессии пользователя
     * @param string|null $secretKey      Секретный ключ сессии
     */
    public function __construct($apiServer, $applicationKey, $sessionKey = null, $secretKey = null)
    {
        $this->setApiServer($apiServer);
        $this->setApplicationKey($applicationKey);
        $this->setSessionKey($sessionKey);
        $this->setSecretKey($secretKey);
    }

    /**
     * Установка адреса API сервера
     *
     * @param string $apiServer
     */
    public function setApiServer($apiServer)
    {
        $this->_apiServer = trim($apiServer);
    }

    /**
     * Получение адреса API сервера
     *
     * @return string
     */
    public function getApiServer()
    {
        return $this->_apiServer;
    }

    /**
     * Установка ключа приложения
     *
     * @param string $applicationKey
     */
    public function setApplicationKey($applicationKey)
    {
        $this->_applicationKey = $applicationKey;
    }

    /**
     * Получение ключа приложения
     *
     * @return string
     */
    public function getApplicationKey()
    {
        return $this->_applicationKey;
    }

    /**
     * Установка ключа сессии текущего пользователя
     *
     * @param string $sessionKey
     */
    public function setSessionKey($sessionKey)
    {
        $this->_sessionKey = $sessionKey;
    }

    /**
     * Получение ключа сессии текущего пользователя
     *
     * @return string
     */
    public function getSessionKey()
    {
        return $this->_sessionKey;
    }

    /**
     * Установка секретного ключа сессии
     *
     * @param string $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->_secretKey = $secretKey;
    }

    /**
     * Получение секретного ключа сессии
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->_secretKey;
    }

    /**
     * Установка идентификатор запроса
     *
     * @param string $callId
     */
    public function setCallId($callId)
    {
        $this->_callId = $callId;
    }

    /**
     * Получение идентификатора запроса
     *
     * @return string
     */
    public function getCallId()
    {
        if (null === $this->_callId) {
            //Генерируем новый идентификатор из текущего времени
            $mkTime = str_replace('.', '', microtime(true));
            //Возвращаем текущее время с префиксом a (auto)
            return 'a' . $mkTime;
        }
        return $this->_callId;
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
     * Получение "чистого" идентификатора текущего пользователя
     *
     * @return string
     */
    public function getLoggedInUser()
    {
        //Формирование параметров запроса
        $request = array(
            'application_key' => $this->getApplicationKey(),
            'session_key' => $this->getSessionKey()
        );
        //Выполнение запроса
        $response = $this->_sendRequest('users.getLoggedInUser', $request);
        //Отдаем ответ
        return $response;
    }

    /**
     * Получение данных пользователя из соц. сети Odniklassniki
     *
     * @param string     $uid          Идентификатор пользователя в системе соц. сети
     * @param array|null $fields       Параметры которые необходимо получить
     * @param bool       $emptyPicture Если true, не возвращает изображения Odnoklassniki по умолчанию, когда фотография пользователя недоступна
     *
     * @return array
     */
    public function getUserInfo($uid, $fields = null, $emptyPicture = true)
    {
        //Формирование списка полей в данных пользователя
        if (!is_array($fields) || !count($fields)) {
            $fields = array('name','pic_1');
        }

        //Формирование параметорв запроса
        $request = array(
            'application_key' => $this->getApplicationKey(),
            'session_key' => $this->getSessionKey(),
            'uids' => $uid,
            'fields' => implode(',', $fields),
            'emptyPictures' => $emptyPicture
        );
        //Выполнение запроса
        $response = $this->_sendRequest('users.getInfo', $request);
        //Отдаем ответ
        return $response;
    }

    /**
     * Вычисление подписи запроса
     *
     * @param string $httpRequestParams
     *
     * @return string
     */
    protected function _signature($httpRequestParams)
    {
        //Удаляем амперсанды
        $httpRequestParams = str_replace('&', '', $httpRequestParams);
        //Оборачиваем знак '=' пробелами
        $httpRequestParams = str_replace('=', ' = ', $httpRequestParams);
        //Вычисляем md5-хэш строки
        return md5($httpRequestParams . $this->getSecretKey());
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
        //Сортировка параметров по ключу в алфавитном порядке
        ksort($params, SORT_STRING);
        //Формирование строки запроса
        $httpRequest = array();
        foreach($params as $key => $val) {
            $httpRequest[] = $key . '=' . $val;
        }

        //Возвращаем строку параметров запроса
        return implode('&', $httpRequest);
    }

    /**
     * Выполнение запроса
     *
     * @param string $method        Метод
     * @param array  $requestParams Параметры запроса
     *
     * @return mixed
     */
    protected function _sendRequest($method, array $requestParams)
    {
        //Добавление метода в параметры запроса запрос
        $requestParams['method'] = $method;
        //Формирование строки запроса
        $httpRequestParams = $this->_buildHttpRequest($requestParams);
        //Формирование и добавление подписи запроса
        $sign = $this->_signature($httpRequestParams);
        $httpRequestParams .= '&sig=' . $sign;

        //Создание запроса
        $url = str_replace('//', '/', $this->getApiServer() . self::URI);
        Zend_Registry::get('log')->info('Odnoklassniki request: ' . $url . '?' . $httpRequestParams);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $httpRequestParams);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //Выполнение запроса
        $response = curl_exec($ch);
        Zend_Registry::get('log')->info('Odnoklassniki response: ' . $response);
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

        //Проверка наличия ошибки в ответе сервера
        if (is_array($response) && isset($response['error_code']) && $response['error_code']) {
            //Установка данных ошибки
            $this->setErrorNo($response['error_code']);
            $this->setErrorMsg($response['error_msg']);
        }

        //Возвращаем ответ
        return $response;
    }
}
