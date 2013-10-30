<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.09.12
 * Time: 11:24
 *
 * API для работы с пользователями (для не соц. сетей, т.е. для интеграции флэша в HTML)
 */
class Core_Api_DataService_User extends Core_Api_DataService_Abstract
{

    /**
     * Установка настроек
     *
     * @param array $options
     * @return Core_Api_DataService_Abstract
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (is_array($this->getOption('user'))) {
            foreach($this->getOption('user') as $name => $value) {
                $this->setOption($name, $value);
            }
        }
    }

    /**
     * Регистрация пользователя
     *
     * @param string $nameSevice Наименование соц. сети (платформы)
     * @param array  $data       Регистрацинные данные пользователя
     * @return array
     * @throws Exception
     */
    public function registration($nameSevice, array $data)
    {
        try {
            return $this->_getCLient()->registration($nameSevice, $data);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API user error:' . PHP_EOL . $ex);
            }
            throw $ex;
        }
    }

    /**
     * Обновление регистрационных данных пользователя
     *
     * @param string $sid  Идентификатор сессии пользователя
     * @param array  $data Массив данных пользовтаеля для обновления
     * @return array Обновленные данные пользователя
     * @throws Exception
     */
    public function update($sid, array $data)
    {
        try {
            return $this->_getCLient()->update($sid, $data);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API user error:' . PHP_EOL . $ex);
            }
            throw $ex;
        }
    }

    /**
     * Аутентификаци пользователя
     *
     * @param string $nameSevice Наименование соц. сети (платформы)
     * @param string $login      Логин пользователя
     * @param string $password   Пароль пользователя
     * @return array Данные зарегистрированного пользователя и идентификатор сессии
     * @throws Exception
     */
    public function auth($nameSevice, $login, $password)
    {
        try {
            return $this->_getCLient()->auth($nameSevice, $login, $password);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API user error:' . PHP_EOL . $ex);
            }
            throw $ex;
        }
    }

    /**
     * Получение данных пользователя по идентификатору сессии
     *
     * @param string $sid Идентификатор сессии
     * @return array
     * @throws Exception
     */
    public function session($sid)
    {
        try {
            return $this->_getCLient()->session($sid);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API user error:' . PHP_EOL . $ex);
            }
            throw $ex;
        }
    }

}
