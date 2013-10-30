<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 02.10.12
 * Time: 15:56
 *
 * Обработчик создания подарка другу в 100 фишек
 */
class App_Service_Handler_Lobby_Gift extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @throws Core_Exception
     * @return string
     */
    public function handle()
    {
        //Получение имени сервиса социальной сети
        $service = $this->getRequest()->get('service');
        //Получение объекта пользователя соц. сети
        $socialUser = new Core_Social_User($service, $this->getRequest()->get('vars'));

        //Проверка баланса пользователя
        $api = new Core_Api_DataService_Balance();
        $balance = $api->getUserBalance($socialUser->getId(), $socialUser->getNetwork());
        if ($balance < 100) {
            throw new Core_Exception('Not enough money to make gift', 501, Core_Exception::USER);
        }

        //API работы с подарками
        $api = new Core_Api_DataService_Gift();
        //Создание подарка в 100 фишек
        $api->create(
            $socialUser->getNetwork(),
            $socialUser->getId(),
            $this->getRequest()->get('friendId'),
            'chips100'
        );

        //Списание суммы подарка со счета пользователя
        $api = new Core_Api_DataService_Balance();
        $api->charge($socialUser->getId(), $socialUser->getNetwork(), 100);

        //Отдаем ответ сервера
        return $this->view->render();
    }
}
