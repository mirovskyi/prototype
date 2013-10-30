<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 21.03.12
 * Time: 17:27
 *
 * Класс (одиночка) клиента API платежей
 */
class Core_Api_DataService_Payment extends Core_Api_DataService_Abstract
{

    /**
     * Установка настроек
     *
     * @param array $options
     * @return Core_Api_DataService_Payment
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (is_array($this->getOption('payment'))) {
            foreach($this->getOption('payment') as $name => $value) {
                $this->setOption($name, $value);
            }
        }
        return $this;
    }

    /**
     * Получение списка купленных пользователем товаров
     *
     * @param string $idServiceUser Идентификатор пользователя в системе соц. сети
     * @param string $nameService   Имя сервиса социальной сети
     * @param int    $itemId        Идентификатор товара в магазине
     *
     * @throws Core_Api_Exception
     * @return array Данные созданного платежа (productId, productName, amount)
     */
    public function create($idServiceUser, $nameService, $itemId)
    {
        try {
            //Вызов удаленного метода
            return $this->_getCLient()->create($idServiceUser, $nameService, $itemId);
        } catch (Exception $e) {
            //Пишем лог
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API payment create error: ' . $e);
            }
            //Выбрасываем исключение
            throw new Core_Api_Exception('Failed to create payment', $e->getCode());
        }
    }

    /**
     * Подтверждение платежа
     *
     * @param string $idServiceUser Идентификатор пользователя в системе соц. сети
     * @param string $nameService   Имя сервиса социальной сети
     * @param $transId              Идентификатор транзакции на стороне соц. сети
     * @param int $paymentId        Идентификатор платежа на сервере балансов
     *
     * @throws Core_Api_Exception
     * @return string Статус успешного платежа ('ok')
     */
    public function success($idServiceUser, $nameService, $transId, $paymentId)
    {
        try {
            //Вызов удаленного метода
            return $this->_getCLient()->success($idServiceUser, $nameService, $transId, $paymentId);
        } catch (Exception $e) {
            //Пишем лог
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API payment success error: ' . $e);
            }
            //Выбрасываем исключение
            throw new Core_Api_Exception('Failed to verification payment', $e->getCode());
        }
    }

}
