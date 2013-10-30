<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.09.12
 * Time: 19:11
 *
 * Обработчик запроса начисления ежедневного бонуса
 */
class App_Service_Handler_Bonus_Daily extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    public function handle()
    {
        //Получение имени сервиса социальной сети
        $service = $this->getRequest()->get('service');
        //Получение объекта пользователя соц. сети
        $socialUser = new Core_Social_User($service, $this->getRequest()->get('vars'));

        //API начисления бонусов
        $api = new Core_Api_DataService_Bonus();
        //Попытка начисления бонуса
        $balance = $api->addDailyBonus($socialUser->getId(), $service);

        //Передача в шаблон ответа текущий баланс пользователя
        $this->getView()->assign('balance', $balance);
        //Возвращаем ответ сервера
        return $this->getView()->render();
    }
}
