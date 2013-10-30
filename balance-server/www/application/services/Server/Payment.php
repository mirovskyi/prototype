<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 31.08.12
 * Time: 10:34
 *
 * Сервис работы с платежами
 */
class App_Service_Server_Payment
{

    /**
     * Идентификатор пользователя в соц. сети
     *
     * @var string
     */
    protected $_idServiceUser;

    /**
     * Имя сервиса соц. сети
     *
     * @var string
     */
    protected $_nameService;

    /**
     * Объект модели пользователя
     *
     * @var App_Model_User
     */
    protected $_user;

    /**
     * Объект платежа
     *
     * @var App_Model_Payment
     */
    protected $_payment;


    /**
     * Создание нового объекта
     *
     * @param string $idServiceUser Идентификатр пользователя в соц. сети
     * @param string $nameService Имя сервиса соц. сети
     */
    public function __construct($idServiceUser, $nameService)
    {
        $this->_idServiceUser = $idServiceUser;
        $this->_nameService = $nameService;
    }

    /**
     * Установка объекта модели пользователя
     *
     * @param App_Model_User $user
     * @return App_Service_Server_Balance
     */
    public function setUser(App_Model_User $user)
    {
        $this->_user = $user;
        return $this;
    }

    /**
     * Получение объекта модели пользователя
     *
     * @return App_Model_User
     */
    public function getUser()
    {
        if (null === $this->_user) {
            //Получение объекта данных пользователя
            $info = new App_Service_Server_Info();
            $user = $info->getUserInfo($this->_idServiceUser, $this->_nameService);
            //Установка объекта модели пользователя
            $this->setUser($user);
        }

        return $this->_user;
    }

    /**
     * Установка объекта платежа
     *
     * @param App_Model_Payment $payment
     *
     * @return App_Service_Server_Payment
     */
    public function setPayment(App_Model_Payment $payment)
    {
        $this->_payment = $payment;
        return $this;
    }

    /**
     * Получение объекта платежа
     *
     * @return App_Model_Payment
     */
    public function getPayment()
    {
        return $this->_payment;
    }

    /**
     * Создание платежа
     *
     * @param int $itemId Идентификатор товара в магазине (shop_items)
     *
     * @return int Идентификатор транзакции
     * @throws Zend_Exception
     */
    public function create($itemId)
    {
        //Получение информации о товаре в магазине
        $shopItem = new App_Model_ShopItem();
        if (null == $shopItem->find($itemId)->getId()) {
            throw new Zend_Exception('Item \'' . $itemId . '\' does not exists');
        }

        //Получение данных товара
        $item = new App_Model_Item();
        $item->find($shopItem->getIdItem());

        //Проверка наличия купленного пользователем товара
        $shop = new App_Service_Server_Shop();
        $shop->setUser($this->getUser());
        $userItem = $shop->getUserItem($item);
        if ($userItem && $userItem->getDeadline() == 0) {
            //Пользователь уже купил товар на неограниченный срок
            throw new Zend_Exception('User allready has the item \'' . $item->getName() . '\'');
        }

        //Создание объекта платежа
        $payment = new App_Model_Payment();
        $payment->setIdUser($this->getUser()->getId())
                ->setIdShopItem($shopItem->getId())
                ->setAmount($shopItem->getMoney())
                ->setIdService($this->getUser()->getIdService())
                ->setTransDate(date('Y-m-d H:i:s'));
        if (!$payment->save()) {
            throw new Zend_Exception('Failed to create payment record in database');
        }
        //Установка объекта платежа
        $this->setPayment($payment);

        //Возвращаем данные покупки
        return array(
            'productId' => $payment->getId(),
            'productName' => $item->getName(),
            'amount' => $payment->getAmount()
        );
    }

    /**
     * Подтверждение платежа
     *
     * @param int $transId Идентификатор транзакции на стороне соц. сети
     * @param int $itemId Идентификатор товара в магазине (shop_items)
     *
     * @throws Zend_Exception
     * @return int Идентификатор транзакции
     */
    public function success($transId, $paymentId)
    {
        return false;
//        $notification = new App_Service_Server_Payment_Notification($this->_nameService);
//        $params = array('paymentId' => $paymentId,
//                        'transId' => $transId,
//                        'uidService' => $this->_idServiceUser);
//
//        //Обоработка платежа
//        if ($notification->success($params)) {
//            return true;
//        } else {
//            throw new Zend_Exception('Error processing payment id:'.$paymentId);
//        }
    }

}
