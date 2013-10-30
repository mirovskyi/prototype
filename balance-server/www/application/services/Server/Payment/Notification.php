<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 31.08.12
 * Time: 13:36
 *
 * Класс обработки оповещения о результате оплаты транзакции
 */
class App_Service_Server_Payment_Notification
{

    /**
     * Наименование сервиса соц. сети
     *
     * @var string
     */
    protected $_service;


    /**
     * Создание нового объекта
     *
     * @param string $service Наименование сервиса соц. сети
     */
    public function __construct($service)
    {
        $this->setService($service);
    }

    /**
     * Установка наименования сервиса соц. сети
     *
     * @param string $serviceName
     * @return void
     */
    public function setService($serviceName)
    {
        $this->_service = $serviceName;
    }

    /**
     * Получение наименования сервиса соц. сети
     *
     * @return string
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     *
     *
     * @param array $requestParams
     * @return array
     * @throws Zend_Exception
     */
    public function init(array $requestParams)
    {
        //Объект обработчика оповещений
        $impl = $this->_getImplementation();
        //Установка параметров запроса
        $impl->setRequestParams($requestParams);

        //Иницифлизация платежа
        $response = $impl->init();

        if (is_array($response)) {
            return $response;
        } else {
            throw new Zend_Exception('Error payment initialization');
        }

    }

    /**
     * Обработка оповещения об успешном платеже
     *
     * @param array $requestParams Массив параметров запроса оповещения
     *
     * @throws Zend_Exception
     * @return App_Model_Payment|bool
     */
    public function success(array $requestParams)
    {
        //Объект обработчика оповещений
        $impl = $this->_getImplementation();
        //Установка параметров запроса
        $impl->setRequestParams($requestParams);
        //Обработка оповещения
        $payment = $impl->success();

        //Если оповещение не обработано либо дублирующее - отдаем результат обработки без создания покупки пользователя
        if (!$payment || $impl->isRepeat()) {
            return $payment;
        } else {
                //Получаем обект модели данных пользователя
                $user = new App_Model_User();
                if (!$user->find($payment->getIdUser())->getId()) {
                    throw new Zend_Exception('Can\'t find user id ' . $payment->getIdUser(), 22);
                }

                //Сервис работы с магазином
                $shopService = new App_Service_Server_Shop();
                $shopService->setUser($user);

                //Покупка товара (добавление товара магазина пользователю)
                $shopService->buy($payment->getIdShopItem());

                //Обновляем статус транзакции в 'SUC'
                $payment->setStatus('SUC')
                        ->setTransDate(date("Y-m-d H:i:s"));
                if (!$payment->save()) {
                    throw new Zend_Exception('Can not update status transaction to \'SUC\'', 2);
                }

                //Возвращаем данные транзакции
                return $payment;
        }
    }

    /**
     * Получение объекта класса реализации обработки оповещения о результате платежа (конкретной соц. сети)
     *
     * @return App_Service_Server_Payment_NotificationInterface
     * @throws Zend_Exception
     */
    private function _getImplementation()
    {
        //Формирование имени класса
        $className = __CLASS__ . '_' . ucfirst($this->getService());
        //Проверка наличия класса
        if (!class_exists($className)) {
            throw new Zend_Exception('Not found implementation \'' . $className .' for payment notification');
        }

        //Создание объекта класса обработки оповещения
        return new $className();
    }

}
