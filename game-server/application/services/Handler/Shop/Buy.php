<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 11:03
 *
 * Обработка запроса покупки товара в магазине
 */
class App_Service_Handler_Shop_Buy extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @throws Core_Protocol_Exception
     * @return string
     */
    public function handle()
    {
        //Проверка наличия сессии пользовтаеля
        if ($this->hasUserSession()) {
            //Получаем данные пользователя соц. сети
            $socialUser = $this->getUserSession()->getSocialUser();
            $service = $socialUser->getNetwork();
        } else {
            //Получаем данные пользователя соц. сети из запроса
            $service = $this->getRequest()->get('service');
            $socialUser = new Core_Social_User($service, $this->getRequest()->get('vars'));
        }
        //Проверка целостности данных
        if (!$service || !$socialUser->getId()) {
            throw new Core_Protocol_Exception('Not all required parameters are received', 638, Core_Exception::USER);
        }

        //Идентификатор товара в магазине
        $item = $this->getRequest()->get('item');
        //API магазина
        $shop = new Core_Api_DataService_Shop();

        //Проверка типа оплате (фишки либо в валюте соц. сети)
        if ($this->getRequest()->get('money') == 'chips') {
            //Покупка за фишки
            $shop->buy($socialUser->getId(), $service, $item);
        } else {
            //Покупка за валюту соц. сети, создание платежа
            $payment = new Core_Api_DataService_Payment();
            $paymentInfo = $payment->create($socialUser->getId(), $service, $item);
            //Передача в шаблон ответа данные о созданном платеже
            $this->view->assign('payment', $paymentInfo);
        }

        //Если запрос был из лобби (без данных сессии пользователя), отдаем обновленные данные магазина
        if (!$this->hasUserSession()) {
            //Получаем список товаров с данными о покупках пользователя
            $items = $shop->getAllItemsWithUserPurchase($socialUser->getId(), $service);
            //Передача данных товаров в шаблон
            $this->view->assign('items', $items);
        }

        //Установка пути к шаблону ответа
        $this->view->setTemplate('shop/open');
        //Возвращаем ответ сервера
        return $this->view->render();
    }

}
