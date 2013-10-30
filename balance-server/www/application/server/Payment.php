<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 31.08.12
 * Time: 10:11
 *
 * Класс описывающий методы сервиса для работы платежами
 */
class App_Server_Payment
{

    /**
     * Метод создания платежа
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     * @param string $itemId        Идентификатор товара в магазине
     *
     * @return array Возвращает массив данных платежа (productId, productName, amount)
     */
    public function create($idServiceUser, $nameService, $itemId)
    {
        //Сервис создания платежей
        $service = new App_Service_Server_Payment($idServiceUser, $nameService);
        //Создание платежа, возвращаем идентификатор транзакции
        return $service->create($itemId);
    }

    /**
     * Метод подтверждения платежа
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     * @param $transId              Идентификатор транзакции на стороне соц. сети
     * @param int $paymentId        Идентификатор платежа на сервере балансов
     *
     * @return array Возвращает массив данных платежа (productId, productName, amount)
     */
    public function success($idServiceUser, $nameService, $transId, $paymentId)
    {
        //Сервис создания платежей
        $service = new App_Service_Server_Payment($idServiceUser, $nameService);
        //Подтверждение платежа
        return $service->success($transId, $paymentId);
    }

}
