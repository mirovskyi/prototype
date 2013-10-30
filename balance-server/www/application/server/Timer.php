<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.09.12
 * Time: 15:42
 *
 * Класс описывающий методы сервиса для работы с таймерами событий пользователя
 */
class App_Server_Timer
{

    /**
     * Получение остатка времени для активации ежечасного бонуса
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     *
     * @return int
     */
    public function getHourBonus($idServiceUser, $nameService)
    {
        //Сервис работы с таймерами
        $service = new App_Service_Server_Timer($idServiceUser, $nameService);
        //Возвращаем остаток времени для активации ежечасного бонуса
        return $service->getHourBonusRestSeconds();
    }

    /**
     * Получение остатка времени для активации ежедневного бонуса
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     *
     * @return int
     */
    public function getDailyBonus($idServiceUser, $nameService)
    {
        //Сервис работы с таймерами
        $service = new App_Service_Server_Timer($idServiceUser, $nameService);
        //Возвращаем остаток времени для активации ежедневного бонуса
        return $service->getDailyBonusRestSeconds();
    }

    /**
     * Получение остатка времени для активации возможности подарить падарок другу
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     *
     * @return int
     */
    public function getFriendPresent($idServiceUser, $nameService)
    {
        //Сервис работы с таймерами
        $service = new App_Service_Server_Timer($idServiceUser, $nameService);
        //Возвращаем остаток времени для активации возможности подарить падарок другу
        return $service->getFriendPresentRestSeconds();
    }

    /**
     * Получение данных таймеров пользовтаеля
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     *
     * @return array
     */
    public function getData($idServiceUser, $nameService)
    {
        //Сервис работы с таймерами
        $service = new App_Service_Server_Timer($idServiceUser, $nameService);
        //Возвращаем массива данных таймеров
        return $service->getDataInArray();
    }

}
