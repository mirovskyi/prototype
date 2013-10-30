<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 02.10.12
 * Time: 11:59
 *
 * Класс описывающий методы сервиса для работы с подарками
 */
class App_Server_Gift
{

    /**
     * Подарить другу подарок
     *
     * @param string $nameService       Наименование соц. сети
     * @param string $idServiceUserFrom Идентификатор пользователя в соц. сети (который дарит подарок)
     * @param string $idServiceUserTo   Идентификатор пользователя в соц. сети (который получает подарок)
     * @param string $name              Название подарка
     *
     * @return bool
     */
    public function create($nameService, $idServiceUserFrom, $idServiceUserTo, $name)
    {
        //Модель обработки запроса
        $model = new App_Service_Server_Gift();
        //Созлание объекта подарка
        $model->create($nameService, $idServiceUserFrom, $idServiceUserTo, $name);
        //Возвращаем ответ
        return true;
    }

    /**
     * Подтверждение выдачи подарка
     *
     * @param string $nameService       Наименование соц. сети
     * @param string $idServiceUserFrom Идентификатор пользователя в соц. сети (который дарит подарок)
     * @param string $idServiceUserTo   Идентификатор пользователя в соц. сети (который получает подарок)
     * @param string $name              Название подарка
     *
     * @return bool
     */
    public function confirm($nameService, $idServiceUserFrom, $idServiceUserTo, $name)
    {
        //Модель обработки запроса
        $model = new App_Service_Server_Gift();
        //Обработка запроса
        return $model->confirm($nameService, $idServiceUserFrom, $idServiceUserTo, $name);
    }

}
