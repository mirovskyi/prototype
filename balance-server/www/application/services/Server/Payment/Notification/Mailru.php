<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 31.08.12
 * Time: 15:01
 *
 * Реализация обработчика оповещения о результате платежа от соц. сети mailru
 */
class App_Service_Server_Payment_Notification_Mailru implements App_Service_Server_Payment_NotificationInterface
{
    /**
     * Имя социальной сети
     */
    const SNAME = 'mailru';

    /**
     * Идентификатор игрока в соц. сети
     *
     * @var int
     */
    protected $_uidService;

    /**
     * Идентификатор платежа в базе данных
     *
     * @var int
     */
    protected $_paymentId;

    /**
     * Идентификатор платежа в системе соц. сети
     *
     * @var string
     */
    protected $_transId;

    /**
     * Флаг дублирующего запроса
     *
     * @var bool
     */
    protected $_repeat = false;

    /**
     * Установка идентификатора игрока соц. сети
     *
     * @param $uidService
     * @return void
     * @internal param int $_uidService
     */
    public function setUidService($uidService)
    {
        $this->_uidService = $uidService;
    }

    /**
     * Получение идентификатора игрока соцю сети
     *
     * @return int
     */
    public function getUidService()
    {
        return $this->_uidService;
    }

    /**
     * Установка идентификатора платежа в базе данных
     *
     * @param int $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->_paymentId = $paymentId;
    }

    /**
     * Получение идентификатора платежа в базе данных
     *
     * @return int
     */
    public function getPaymentId()
    {
        return $this->_paymentId;
    }

    /**
     * Установка идентификатора платежа в системе соц. сети
     *
     * @param string $transId
     */
    public function setTransId($transId)
    {
        $this->_transId = $transId;
    }

    /**
     * Получение идентификатора платежа в системе соц. сети
     *
     * @return string
     */
    public function getTransId()
    {
        return $this->_transId;
    }

    /**
     * Проверка подписи запроса
     *
     * @param array $params
     * @throws Zend_Exception
     * @return \App_Model_Payment|bool|void
     */
    public function checkRequestSignature(array $params) {
        //Получаем значение секретного ключа приложения
        if (Zend_Registry::isRegistered('serverconfig')) {
            $options = Zend_Registry::get('serverconfig')->get('application');
        } else {
            $options = Core_Server::getInstance()->getOption('application');
        }

        $service = self::SNAME;
        $sKey = $options->$service->secretKey;

        //Удаляем не нужные параметры из запроса
        unset($params['action']);
        unset($params['controller']);
        unset($params['module']);
        unset($params['signature']);

        //Сортировка по ключам
        ksort($params);

        $sign = '';
        foreach($params as $key=>$val) {
            if ($key != 'sig') {
                $sign .= $key .'='. $val;
            }
        }

        $sign = md5($sign . $sKey);

        if ($sign === $params['sig']) {
            return true;
        } else {
            throw new Zend_Exception('Wrong signature request');
        }
    }

    /**
     * Установка параметров запроса оповещения
     *
     *
     * @param array $params Массив параметров запроса
     *
     * @return void
     */
    public function setRequestParams(array $params)
    {
        //Если запрос от соц. сети, проверяем подпись запроса
        $this->checkRequestSignature($params);

        if (isset($params['service_id'])) {
            $this->setPaymentId($params['service_id']);
        }
        if (isset($params['transaction_id'])) {
            $this->setTransId($params['transaction_id']);
        }
        if (isset($params['uid'])) {
            $this->setUidService($params['uid']);
        }
    }

    /**
     * Инициализация платежа
     *
     * @return mixed|void
     */
    public function init() {

    }

    /**
     * Обработка оповещения об успешной оплате платежа
     *
     * @return App_Model_Payment|bool
     */
    public function success()
    {
        //Получение объекта платежа
        $payment = new App_Model_Payment();
        if (!$payment->find($this->getPaymentId())->getId()) {
            throw new Zend_Exception('Payment id:' . $this->getPaymentId() . ' not find', 702);
        }

        //Проверка статуса платежа
        if ($payment->getStatus() == 'SUC') {
            //Установка флага дублирующего запроса
            $this->_repeat = true;
            //Платеж уже был обработан
            return $payment;
        }

        //Меняем статус транзакции на 'PROC'
        $payment->setStatus('PROC')
                ->setTransDate(date("Y-m-d H:i:s"))
                ->setStransId($this->getTransId());
        if (!$payment->save()) {
            throw new Zend_Exception('Can not update status transaction to \'PROC\'', 2);
        }

        return $payment;
    }

    /**
     * Флаг дублирующего запроса успешности платежа
     *
     * @return bool
     */
    public function isRepeat()
    {
        return $this->_repeat;
    }
}
