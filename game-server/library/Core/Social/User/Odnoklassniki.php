<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 24.11.12
 * Time: 16:33
 *
 * Класс установки дополнительных данных пользователя соц. сети Odnoklassniki
 */
class Core_Social_User_Odnoklassniki implements Core_Social_User_InfoInterface
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

        //Объект API соц. сети
        $api = new Core_Api_Social_Odnoklassniki(
            $params['api_server'],
            $params['application_key'],
            $params['session_key'],
            $params['session_secret_key']
        );

        //Получение данных пользователя
        $response = $api->getUserInfo($params['logged_user_id']);
        //Проверка наличия ошибки
        if ($api->getErrorNo()) {
            Zend_Registry::get('log')->err('Odnoklassniki error: ' . $api->getErrorNo() . ' ' . $api->getErrorMsg());
            return;
        }
        if (!is_array($response) || !isset($response[0])) {
            Zend_Registry::get('log')->err('Odnoklassniki error, user.getInfo invalid response: ' . print_r($response, true));
            return;
        }

        //Установка данных пользователя
        $userInfo = $response[0];
        if (isset($userInfo['name'])) {
            $params['name'] = $userInfo['first_name'] . ' ' . $userInfo['last_info'];
        }
        if (isset($userInfo['pic_1'])) {
            $params['photo'] = $userInfo['pic_1'];
        }
    }
}
