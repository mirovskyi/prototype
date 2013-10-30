<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 31.08.12
 * Time: 16:46
 * To change this template use File | Settings | File Templates.
 */
class Core_SocialNetwork_Vkontakte_Api implements Core_SocialNetwork_ApiInterface
{
    /**
     * Имя социальной сети
     */
    const SNAME = 'vkontakte';

    /**
     * @var $apiURL Адрес API соуиальной сети
     */
    public $_apiURL;

    /**
     * @var $appID  Идентификатор соц. сети
     */
    private $_appID;

    /**
     * @var $sKey   Секретный ключ приложения
     */
    private $_sKey;

    /**
     * Массив статусов ошибок методов API
     * @var array
     */
    public $_errStatus = array(
        'secure.withdrawVotes' => array(
            1     => 'Unknown error occurred',
            2     => 'Application is disabled. Enable your application or use test mode',
            4     => 'Incorrect signature',
            6     => 'Too many requests per second',
            8     => 'Invalid request',
            113   => 'Invalid user id',
            151   => 'Invalid votes',
            500   => 'Permission denied. You must enable votes processing in application settings',
            502   => 'Not enough votes on user\'s balance'
        )
    );


    public function __construct()
    {
        $options = Core_Server::getInstance()->getOption('application');

        if ($options[self::SNAME]) {
            $options = $options[self::SNAME];

            $this->_apiURL = $options['url'];
            $this->_appID = $options['appId'];
            $this->_sKey = $options['secretKey'];
        } else {
            throw new Zend_Exception('No configs for API');
        }
    }

    /**
     * Вызов метода соцю сети
     *
     * @param $method           Название метода
     * @param array $params     Массив дополнительных параметров
     * @return mixed
     * @throws Zend_Exception
     */
    public function getApiMethod($method, $params = array())
    {
        if (!empty($method)) {
            //Определяем параметры запроса
            $params['api_id'] = $this->_appID;
            $params['v'] = '3.0';
            $params['method'] = $method;
            $params['timestamp'] = time();
            $params['format'] = 'json';
            $params['random'] = rand(0,10000);

            ksort($params);

            //Получаем значение подписи
            $params['sig'] = $this->getSignature($params);
            $params = http_build_query($params);
            $query = $this->_apiURL .'?'. $params;

            //Выполгяем http запрос
            $res = $this->sendCurlRequest($query);

            return $res;

        } else {
            throw new Zend_Exception('Undefined API method');
        }
    }

    /**
     * Получение подписи параметров
     *
     * @param array $params Массив параметров
     * @return string
     */
    protected  function getSignature(array $params)
    {
        $sig = '';

        //Формируем сторку для подписи
        foreach($params as $key=>$val) {
            $sig .= $key .'='. $val;
        }

        $sig .= $this->_sKey;

        //Получаем значение подписи
        $sig = md5($sig);

        return $sig;
    }

    /**
     * Отправка hhtp запроса используя libcurl
     *
     * @param $strLink          Строка url запроса
     * @param string $method    Метод http запроса
     * @return mixed
     */
    protected function sendCurlRequest($strLink, $method = 'get')
    {
        //Логирование запроса
        $this->_log("CURL Request: ".$strLink);

        // выполняем curl запрос
        $ch = curl_init();

        // determine method used
        if ($method == 'get') {
            curl_setopt($ch, CURLOPT_URL, $strLink);
        } elseif (strtolower($method) == 'post') {
            list($url, $params) = explode('?', $strLink);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $strHtml = curl_exec($ch);
        curl_close($ch);


        //Логирование ответа
        $this->_log("CURL Response: ".$strHtml);

        return $strHtml;
    }

    /**
     * Логирование
     *
     * @param string $message
     */
    private function _log($message)
    {
        if (Zend_Registry::getInstance()->isRegistered('log')) {
            Zend_Registry::get('log')->info($message);
        }
    }

    /**
     * Получение имени пользователя в соц. сети
     *
     * @param string $id Идентификатор пользователя в соц. сети
     * @return string
     */
    public function getUserName($id)
    {
        //Запрос получения данных профиля
        $response = $this->getApiMethod('getProfiles', array('uids' => $id));
        //Преобразование ответа в массив
        $arrResponse = json_decode($response, true);
        //Проверка наличия успешного ответа
        if (isset($arrResponse['response']) && count($arrResponse['response'])) {
            //Достаем первый элемент (должен быть только один, т.к. передавали один uid)
            $data = $arrResponse['response'][0];
            //Удаление элемента идентификатора пользователя
            if (isset($data['uid'])) {
                unset($data['uid']);
            }
            //Конкатенация частей имени
            return trim(implode(' ', $data));
        }

        return false;
    }
}
