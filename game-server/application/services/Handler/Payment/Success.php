<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.03.12
 * Time: 11:03
 *
 * Обработка запроса об успешном платеже
 */
class App_Service_Handler_Payment_Success extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
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

        //Получаем идентификатор транзакции в соц. сети
        $transId = $this->getRequest()->get('transId');

        //Получаем идентификатор оплаченой услуги
        $paymentId = $this->getRequest()->get('paymentId');

        //API получения данных магазина
        $payment = new Core_Api_DataService_Payment();

        //Выполняем обработку платежа
        $result = $payment->success($socialUser->getId(), $service, $transId, $paymentId);

        //Возвращаем ответ сервера
        return $this->view->render();
    }

}
