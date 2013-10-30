<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 24.09.12
 * Time: 16:44
 *
 * Контроллер работы с пользователями
 */
class App_Server_User
{

    /**
     * Регистрация пользователя
     *
     * @param string $nameService Наименование платформы (соц. сети)
     * @param array  $data        Данные пользователя
     * @return array
     */
    public function registration($nameService, $data)
    {
        //Сервис работы с пользователями
        $service = new App_Service_Server_User();
        //Регистрация пользователя
        return $service->registration($nameService, $data);
    }

    /**
     * Аутентификатция пользователя
     *
     * @param string $nameService Наименование платформы (соц. сети)
     * @param string $login       Логин пользователя
     * @param string $password    Пароль
     * @return array
     */
    public function auth($nameService, $login, $password)
    {
        //Сервис работы с пользователями
        $service = new App_Service_Server_User();
        //Аутентификация пользователя
        return $service->auth($nameService, $login, $password);
    }

    /**
     * Получение данных пользователя по идентификатору сессии
     *
     * @param string $sid Идентификатор сессии пользователя
     * @return array
     */
    public function session($sid)
    {
        //Сервис работы с пользователями
        $service = new App_Service_Server_User();
        //Получение данных пользователя по сессии
        return $service->session($sid);
    }

    /**
     * Обновление данных пользователя
     *
     * @param string $sid  Идентификатор сессии пользователя
     * @param array  $data Массив данных пользователя для изменения
     * @return array Обновленные данные пользователя
     */
    public function update($sid, $data)
    {
        //Сервис работы с пользователями
        $service = new App_Service_Server_User();
        //Обновление данных пользователя
        return $service->update($sid, $data);
    }
}
