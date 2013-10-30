<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.11.12
 * Time: 21:35
 *
 * Класс установки дополнительных данных пользователя соц. сети Mail.RU
 */
class Core_Social_User_Mailru implements Core_Social_User_InfoInterface
{

    /**
     * Инициалиация параметров пользователя
     *
     * @param array $params  Параметры сесси пользователя соц. сети
     * @param array $configs Конфиги соц. сети
     */
    public function initUserInfo(array &$params, array $configs)
    {
        //Проверка наличия необходимых параметров
        if (isset($params['name']) && isset($params['photo'])) {
            //Необходимые параметры уже есть
            return;
        }

        //Получение объекта работы с API Mail.RU
        $api = new Core_Api_Social_Mailru(
            $configs['apiServer'],
            $params['app_id'],
            $params['session_key'],
            $configs['secretKey']
        );

        //Получения данных текущего пользователя
        $response = $api->getUserInfo($params['vid']);

        //Проверка наличия ошибки
        if ($api->getErrorNo()) {
            Zend_Registry::get('log')->err('Mail.RU error: ' . $api->getErrorNo() . ' ' . $api->getErrorMsg());
            return;
        }
        if (!is_array($response) || !isset($response[0])) {
            Zend_Registry::get('log')->err('Mail.RU error, users.getInfo invalid response: ' . print_r($response,true));
            return;
        }

        //Установка данных пользователя
        $userInfo = $response[0];
        if (isset($userInfo['first_name']) && isset($userInfo['last_name'])) {
            $params['name'] = $userInfo['first_name'] . ' ' . $userInfo['last_name'];
        }
        if (isset($userInfo['pic'])) {
            $params['photo'] = $userInfo['pic'];
        }
    }
}
