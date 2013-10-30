<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 15.02.12
 * Time: 12:46
 *
 * Класс (одиночка) клиента API сервера балансов
 */
class Core_Api_DataService_Balance extends Core_Api_DataService_Abstract
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

        if (is_array($this->getOption('balance'))) {
            foreach($this->getOption('balance') as $name => $value) {
                $this->setOption($name, $value);
            }
        }
    }

    /**
     * Метод получения баланса пользователя
     *
     * @param string $idServiceUser Идентификатор пользователя в системе соц. сети
     * @param string $nameService Имя сервиса социальной сети
     * @return int|bool
     */
    public function getUserBalance($idServiceUser, $nameService)
    {
        try {
            return $this->_getCLient()->get($idServiceUser, $nameService);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API balance error:' . PHP_EOL . $ex);
            }
            return false;
        }
    }

    /**
     * Пополнение баланса игрового счета пользователя
     *
     * @param string $idServiceUser
     * @param string $nameService
     * @param int $amount
     * @return bool
     */
    public function deposit($idServiceUser, $nameService, $amount)
    {
        try {
            return $this->_getCLient()->deposit($idServiceUser, $nameService, $amount);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API deposit balance error:' . PHP_EOL . $ex);
            }
            return false;
        }
    }

    /**
     * Списание средств с игрового счета пользователя
     *
     * @param string $idServiceUser Идентификатор пользователя в системе соц. сети
     * @param string $nameService Имя сервиса социальной сети
     * @param int $amount Сумма списания
     * @return bool
     */
    public function charge($idServiceUser, $nameService, $amount)
    {
        try {
            return $this->_getCLient()->charge($idServiceUser, $nameService, $amount);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API charge balance error:' . PHP_EOL . $ex);
            }
            return false;
        }
    }

}
