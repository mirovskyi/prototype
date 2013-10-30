<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.09.12
 * Time: 18:07
 *
 * Класс описывающий методы работы с бонусами
 */
class App_Server_Bonus
{

    /**
     * Зачисление ежечасного бонуса пользователю
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     *
     * @return int
     */
    public function addHourBonus($idServiceUser, $nameService)
    {
        //Сервис зачисления бонусов
        $service = new App_Service_Server_Bonus($idServiceUser, $nameService);
        //Зачисление бонуса, возвращаем текущий баланс пользователя
        return $service->addHourBonus();
    }

    /**
     * Зачисление ежедневного бонуса пользователю
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     *
     * @return int
     */
    public function addDailyBonus($idServiceUser, $nameService)
    {
        //Сервис зачисления бонусов
        $service = new App_Service_Server_Bonus($idServiceUser, $nameService);
        //Зачисление бонуса, возвращаем текущий баланс пользователя
        return $service->addDailyBonus();
    }

}
