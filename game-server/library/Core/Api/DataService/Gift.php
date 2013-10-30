<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 02.10.12
 * Time: 15:58
 *
 * API для работы с подарками
 */
class Core_Api_DataService_Gift extends Core_Api_DataService_Abstract
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

        if (is_array($this->getOption('gift'))) {
            foreach($this->getOption('gift') as $name => $value) {
                $this->setOption($name, $value);
            }
        }
    }

    /**
     * Создание подарка фишек
     *
     * @param string $nameService       Наименование соц. сети
     * @param string $idServiceUserFrom Идентификатор пользователя в соц. сети, который дарит подарок
     * @param string $idServiceUserTo   Идентификатор пользователя в соц. сети, который получает подарок
     * @param string $name              Название подарка
     *
     * @return bool
     * @throws Core_Api_Exception
     */
    public function create($nameService, $idServiceUserFrom, $idServiceUserTo, $name)
    {
        try {
            return $this->_getCLient()->create($nameService, $idServiceUserFrom, $idServiceUserTo, $name);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API gift error:' . PHP_EOL . $ex);
            }
            throw new Core_Api_Exception($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Подтверждение выдачи подарка
     *
     * @param string $nameService       Наименование соц. сети
     * @param string $idServiceUserFrom Идентификатор пользователя в соц. сети, который дарит подарок
     * @param string $idServiceUserTo   Идентификатор пользователя в соц. сети, который получает подарок
     * @param string $name              Название подарка
     *
     * @return bool
     * @throws Core_Api_Exception
     */
    public function confirm($nameService, $idServiceUserFrom, $idServiceUserTo, $name)
    {
        try {
            return $this->_getCLient()->confirm($nameService, $idServiceUserFrom, $idServiceUserTo, $name);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API gift error:' . PHP_EOL . $ex);
            }
            throw new Core_Api_Exception($ex->getMessage(), $ex->getCode());
        }
    }

}
