<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 15.02.12
 * Time: 11:05
 *
 * Класс описывающий методы сервиса для работы с балансом пользователя
 */
class App_Server_Balance
{

    public function get($idServiceUser, $nameService)
    {
        //Модель обработки запроса
        $model = new App_Service_Server_Balance($idServiceUser, $nameService);
        //Возвращаем текущий баланс пользователя
        return $model->getUserBalance();
    }

    public function deposit($idServiceUser, $nameService, $amount)
    {
        //Модель обработки запроса
        $model = new App_Service_Server_Balance($idServiceUser, $nameService);
        //Пополнение баланса пользователя
        return $model->increaseUserBalance($amount);
    }

    public function charge($idServiceUser, $nameService, $amount)
    {
        //Модель обработки запроса
        $model = new App_Service_Server_Balance($idServiceUser, $nameService);
        //Списание суммы со счета пользователя
        return $model->reduceUserBalance($amount);
    }

}
