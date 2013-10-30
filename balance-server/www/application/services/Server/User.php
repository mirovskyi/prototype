<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 24.09.12
 * Time: 16:47
 *
 * Сервис работы с пользователями
 */
class App_Service_Server_User
{

    /**
     * Метод регистрации пользователя
     *
     * @param string $nameService Наименование платформы (соц. сети)
     * @param array  $data        Данные пользователя
     * @return array Данные пользователя и идентификатор сессии
     */
    public function registration($nameService, $data)
    {
        //Реализация регистрации пользователя
        $registration = new App_Service_Server_User_Registration($nameService, $data);
        //Регистрация
        $registration->registration();

        //Создание сессии пользователя
        $auth = new App_Service_Server_User_Auth();
        $auth->createSession($registration->getUserInfo()->getIdUser());

        //Формируем ответ сервера
        return array(
            'sid' => $auth->getUserSession()->getSid(),
            'data' => $this->_userInfoToArray($registration->getUserInfo())
        );
    }

    /**
     * Аутентификация пользователя
     *
     * @param string $nameService Наименование платформы (соц. сети)
     * @param string $login       Логин пользователя
     * @param string $password    Пароль пользователя
     * @return array Данные пользователя и идентификатор сессии
     */
    public function auth($nameService, $login, $password)
    {
        //Реализация аутентификации пользователя
        $auth = new App_Service_Server_User_Auth($nameService);
        //Аутентификация
        $auth->auth($login, $password);

        //Формируем ответ сервера
        return array(
            'sid' => $auth->getUserSession()->getSid(),
            'data' => $this->_userInfoToArray($auth->getUserInfo())
        );
    }

    /**
     * Метод получения данных пользователя по идентификатору сессии
     *
     * @param string $sid Идентификатор сессии
     * @return array
     */
    public function session($sid)
    {
        //Реализация аутентификации пользователя
        $auth = new App_Service_Server_User_Auth();
        //Поиск сессии
        $auth->findSession($sid);

        //Формируем ответ сервера
        return $this->_userInfoToArray($auth->getUserInfo());
    }

    /**
     * Обновление данных пользователя
     *
     * @param string $sid  Идентификатор сессии пользователя
     * @param array  $data Измененные данные пользователя
     * @return array
     * @throws Core_Exception
     */
    public function update($sid, array $data)
    {
        //Достаем данные пользователя по идентификатору сессии
        $auth = new App_Service_Server_User_Auth();
        $auth->findSession($sid);

        //Логин нельзя изменять
        if (isset($data['login'])) {
            unset($data['login']);
        }
        //Соль пароля нельзя обновлять
        if (isset($data['salt'])) {
            unset($data['salt']);
        }
        //Запоминаем пароль
        $passwd = null;
        if (isset($data['passwd'])) {
            $passwd = $data['passwd'];
            unset($data['passwd']);
        }
        //Установка обновленных данных пользователя
        $auth->getUserInfo()->setOptions($data);
        //Проверка изменения пароля
        if ($passwd) {
            //Инициализация хеша пароля с солью
            $passwd = $auth->getUserInfo()->initHashPassword($passwd);
            $auth->getUserInfo()->setPasswd($passwd);
        }
        //Сохраняем обновленные данные
        if ($auth->getUserInfo()->save()) {
            //Отдаем данные пользователя
            return $this->_userInfoToArray($auth->getUserInfo());
        } else {
            throw new Core_Exception('Error writing to database', 5000);
        }
    }

    /**
     * Получение данных пользователя в виде массива
     *
     * @param App_Model_UserInfo $userInfo
     * @return array
     */
    private function _userInfoToArray(App_Model_UserInfo $userInfo)
    {
        $userData = array(
            'uid' => $userInfo->getId(),
            'login' => $userInfo->getLogin(),
            'email' => $userInfo->getEmail(),
            'phone' => $userInfo->getPhone(),
            'name' => $userInfo->getName(),
            'country' => $userInfo->getCountry(),
            'sex' => $userInfo->getSex(),
            'birth' => $userInfo->getBirth(),
            'lang' => $userInfo->getLang(),
            'mode' => $userInfo->getMode()
        );
        //Добавляем данные баланса пользователя
        if ($userInfo->getMode() > 0) {
            $userData['balance'] = $userInfo->getBalanceReal();
        } else {
            $userData['balance'] = $userInfo->getBalanceFree();
        }

        return $userData;
    }
}
