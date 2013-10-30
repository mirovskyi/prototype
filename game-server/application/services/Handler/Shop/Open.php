<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 10:03
 *
 * Обработчик открытия магазина
 */
class App_Service_Handler_Shop_Open extends App_Service_Handler_Abstract
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

        //Проверка наличия фильтра списка товаров
        $filters = array();
        if ($this->getRequest()->get('filterName')) {
            $filters['name'] = $this->getRequest()->get('filterName');
        }

        //API получения данных магазина
        $shop = new Core_Api_DataService_Shop();
        //Получаем список товаров с данными о покупках пользователя
        $items = $shop->getAllItemsWithUserPurchase($socialUser->getId(), $service, $filters);

        //Передача данных товаров в шаблон
        $this->view->assign('items', $items);
        //Возвращаем ответ сервера
        return $this->view->render();
    }

}
