<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 21.03.12
 * Time: 17:27
 *
 * Класс (одиночка) клиента API сервера магазина
 */
class Core_Api_DataService_Shop extends Core_Api_DataService_Abstract
{

    /**
     * Установка настроек
     *
     * @param array $options
     * @return Core_Api_DataService_Shop
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (is_array($this->getOption('shop'))) {
            foreach($this->getOption('shop') as $name => $value) {
                $this->setOption($name, $value);
            }
        }
        return $this;
    }

    /**
     * Получение списка всех товаров
     *
     * @param array $filters Правила фильтрации списка товаров
     *
     * @return array
     */
    public function getAllItems($filters = array())
    {
        try {
            return $this->_getCLient()->getAllItems($filters);
        } catch (Exception $e) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API shop getAllItems error: ' . $e);
            }
            return array();
        }
    }

    /**
     * Получение списка купленных пользователем товаров
     *
     * @param string $idServiceUser Идентификатор пользователя в системе соц. сети
     * @param string $nameService Имя сервиса социальной сети
     * @return array
     */
    public function getUserItems($idServiceUser, $nameService)
    {
        try {
            return $this->_getCLient()->getUserItems($idServiceUser, $nameService);
        } catch (Exception $e) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API shop getUserItems error: ' . $e);
            }
            return array();
        }
    }

    /**
     * Получение списка всех товаров с данными о покупках пользователя
     *
     * @param string $idServiceUser Идентификатор пользователя в системе соц. сети
     * @param string $nameService   Имя сервиса социальной сети
     * @param array  $filters       Правила фильтрации списка товаров
     *
     * @return array
     */
    public function getAllItemsWithUserPurchase($idServiceUser, $nameService, $filters = array())
    {
        try {
            return $this->_getCLient()->getAllItemsWithUserPurchase($idServiceUser, $nameService, $filters);
        } catch (Exception $e) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API shop getAllItemsWithUserPurchase error: ' . $e);
            }
            return array();
        }
    }

    /**
     * Покупка товара пользователем
     *
     * @param string $idServiceUser Идентификатор пользователя в системе соц. сети
     * @param string $nameService   Имя сервиса социальной сети
     * @param string $item          Наименование товара
     * @param int    $time          Срок действия товара (в секундах)
     * @throws Core_Api_Exception
     * @return bool
     */
    public function buy($idServiceUser, $nameService, $item, $time = 0)
    {
        try {
            return $this->_getCLient()->buy($idServiceUser, $nameService, $item, $time);
        } catch (Exception $e) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API shop buy error: ' . $e);
            }

            if (Core_Protocol_Fault::$_internal[$e->getCode()]) {
                throw new Core_Api_Exception('', $e->getCode(), Core_Exception::USER);
            } else{
                throw new Core_Api_Exception('Unknown api error', $e->getCode(), Core_Exception::USER);
            }
        }
    }

}
