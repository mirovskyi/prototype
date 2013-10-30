<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 15.03.12
 * Time: 15:24
 *
 * Класс описывающий методы сервиса для работы с магазином игрового сервиса
 */
class App_Server_Shop
{

    /**
     * Получение списка всех товаров магазина
     *
     * @param array $filters Правила фильтрации списка товаров
     *
     * @return array
     */
    public function getAllItems($filters = array())
    {
        //Сервис данных магазина
        $service = new App_Service_Server_Shop();
        //Возвращаем список товаров
        return $service->getShopItems();
    }

    /**
     * Получение списка всех купленных пользователем товаров
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование социальной сети
     * @param array  $filters       Правила фильтрации списка товаров
     *
     * @return array
     */
    public function getUserItems($idServiceUser, $nameService, $filters = array())
    {
        //Сервис данных магазина
        $model = new App_Service_Server_Shop($idServiceUser, $nameService);
        //Возвращаем список товаров пользователя
        return $model->getUserItems();
    }

    /**
     * Получение списка всех товаров магазина с данными о покупках пользователя
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование социальной сети
     * @param array  $filters       Правила фильтрации списка товаров
     *
     * @return array
     */
    public function getAllItemsWithUserPurchase($idServiceUser, $nameService, $filters = array())
    {
        //Сервис данных магазина
        $model = new App_Service_Server_Shop($idServiceUser, $nameService);
        //Возвращаем список товаров магазина с данными о покупках пользователя
        return $model->getUserShopItems($filters);
    }

    /**
     * Покупка товара пользователем
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование социальной сети
     * @param string $itemId        Идентификатор товара в магазине
     * @return bool
     */
    public function buy($idServiceUser, $nameService, $itemId)
    {
        //Сервис данных магазина
        $model = new App_Service_Server_Shop($idServiceUser, $nameService);
        //Возвращаем результат покупки
        return $model->buy($itemId, true);
    }

}
