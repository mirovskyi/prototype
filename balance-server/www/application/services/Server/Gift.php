<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 01.10.12
 * Time: 11:16
 *
 * Сервис работы с подарками в игрвом сервисе
 */
class App_Service_Server_Gift
{

    /**
     * Создание объекта подарка
     *
     * @param string $nameService        Наименование соц. сети
     * @param string $idServiceUserFrom  Идентификатор пользователя в соц. сети, подарившего подарок
     * @param string $idServiceUserTo    Идентификатор пользователя в соц. сети, получившего подарок
     * @param string $name               Наименование подарка
     *
     * @throws Core_Exception
     * @return App_Model_Gift
     */
    public function create($nameService, $idServiceUserFrom, $idServiceUserTo, $name)
    {
        //Проверка таймера возможности дарить подарки у пользователя
        $timerService = new App_Service_Server_Timer($idServiceUserFrom, $nameService);
        if ($timerService->getFriendPresentRestSeconds() > 0) {
            //Нет возможности дарить подарок (1 подарок в сутки)
            throw new Core_Exception('Reached the limit of gifts per day');
        }

        //Создания объекта модели подарка
        $gift = new App_Model_Gift();
        $gift->setIdService($timerService->getService()->getId())
             ->setIdUserFrom($idServiceUserFrom)
             ->setIdUserTo($idServiceUserTo)
             ->setName($name)
             ->setCreateDate(date('Y-m-d H:i:s'));
        if (!$gift->save()) {
            throw new Core_Exception('Error writing to database', 5000);
        }

        //Обнуление таймера подарков
        $timerService->resetFriendPresent();

        //Возвращаем объект модели подарка
        return $gift;
    }

    /**
     * Подтверждение выдачи подарка
     *
     * @param $nameService
     * @param $idServiceUserFrom
     * @param $idServiceUserTo
     * @param $name
     *
     * @throws Core_Exception
     * @return bool
     */
    public function confirm($nameService, $idServiceUserFrom, $idServiceUserTo, $name)
    {
        //Получение данных соц. сети по имени
        $service = $this->_getService($nameService);

        //Получаем объект модели подарка
        $gift = new App_Model_Gift();
        $where = $gift->select()->where('id_service = ?', $service->getId())
                                ->where('id_user_from = ?', $idServiceUserFrom)
                                ->where('id_user_to = ?', $idServiceUserTo)
                                ->where('name = ?', $name);
        if (!$gift->fetchRow($where)->getId()) {
            throw new Core_Exception('Gift does not exists');
        }

        //Проверка статуса подарка
        if ($gift->getIsReceived()) {
            //Подарок уже был подтвержден (подарен)
            throw new Core_Exception('Gift has allready received');
        }

        //Получение данных товара в магазине по названию подарка
        $shopItem = $this->_getShopItemByGiftName($name);

        //Получаем данные пользователя, которому преднозначен подарок
        $infoService = new App_Service_Server_Info();
        $user = $infoService->getUserInfo($idServiceUserTo, $nameService);

        //Сервис работы с магазином
        $shopService = new App_Service_Server_Shop();
        $shopService->setUser($user);
        //Добавление подаренного товара пользователю
        $shopService->buy($shopItem->getId());

        //Обновление данных подарка
        $gift->setIsReceived(true);
        $gift->setReceivedDate(date('Y-m-d H:i:s'));
        $gift->save();

        //Установка оповещения пользователя о получении подарка
        $notificationService = new App_Service_Server_Notification();
        $notificationService->addNotification(
            $user->getId(),
            'gift',
            array(
                'name' => $name,
                'fromUserName' => $this->_getUserName($idServiceUserFrom, $nameService)
            )
        );

        //Установка пользователю, подарившему подарок, флаг приглашения друга (если друг пришел в первый раз)
        if ($infoService->isNewUser()) {
            $infoService = new App_Service_Server_Info();
            $infoService->switchUserFlag($gift->getIdUserFrom(), 4, true);
        }

        return true;
    }

    /**
     * Получение объекта модели данных социальной сети по названию
     *
     * @param string $nameService Название соц. сети
     *
     * @return App_Model_Service
     * @throws Core_Exception
     */
    private function _getService($nameService)
    {
        //Получение данных соц. сети
        $service = new App_Model_Service();
        $where = $service->select()->where('name = ?', $nameService);
        if (!$service->fetchRow($where)->getId()) {
            throw new Core_Exception('Social network "' . $nameService . '" does not register in balance service');
        }

        return $service;
    }

    /**
     * Получение объекта данных товара в магазине по названию подарка
     *
     * @param string $name Название подарка
     *
     * @return App_Model_ShopItem|bool
     * @throws Zend_Exception
     */
    private function _getShopItemByGiftName($name)
    {
        $itemName = trim($name);
        //Обработка имени подарка
        if (strstr($itemName, App_Model_Item::CHIPS)) {
            //Получаем количество фишек
            $count = str_replace(App_Model_Item::CHIPS, '', $itemName);
            //Установка наименования товара
            $itemName = App_Model_Item::CHIPS;
        } else {
            //Количество - 1 еденица товара
            $count = 1;
        }

        //Получение данных товара в магазине
        $shopService = new App_Service_Server_Shop();
        $shopItem = $shopService->getShopItem($itemName, $count);
        if (!$shopItem) {
            throw new Zend_Exception('Shop item \'' . $name . '\' does not exists', 4001);
        }

        //Возвращаем объект товара в магазине
        return $shopItem;
    }

    /**
     * Получение имени пользователя соц. сети
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     *
     * @return string
     */
    private function _getUserName($idServiceUser, $nameService)
    {
        //API соц. сети
        $api = Core_SocialNetwork_Api::factory($nameService);
        //Получение имени пользователя
        return $api->getUserName($idServiceUser);
    }
}
