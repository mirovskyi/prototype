<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 17.02.12
 * Time: 17:03
 *
 * Модель получения данных игрового сервиса
 */
class App_Service_Server_Info
{

    /**
     * Флаг создания нового пользователя в БД
     *
     * @var bool
     */
    protected $_isNewUser = false;


    /**
     * Получение массива данных игр
     *
     * @return array
     */
    public function getGames()
    {
        //Получаем модель игры в БД
        $game = new App_Model_Game();

        //Формируем список игр
        $games = array();
        foreach($game->fetchAll() as $item) {
            $games[] = array(
                'name' => $item->getName(),
                'title' => $item->getTitle(),
                'url' => $item->getUrl(),
                'online' => $item->getOnline()
            );
        }

        //Возвращаем список игр
        return $games;
    }

    /**
     * Получение модели данных пользователя
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     * @throws Zend_Exception
     * @return App_Model_User|bool
     */
    public function getUserInfo($idServiceUser, $nameService)
    {
        //Получаем данные соц. сети
        $service = new App_Model_Service();
        $where = $service->select()->where('name = ?', $nameService);
        if ($service->fetchRow($where)->getId() == null) {
            throw new Zend_Exception('Social network "' . $nameService . '" does not register in balance service');
        }
        //Получаем данные пользовавтеля
        $user = new App_Model_User();
        $where = $user->select()->where('id_service_user = ?', $idServiceUser)
                                ->where('id_service = ?', $service->getId());
        $user->fetchRow($where);
        //Проверка наличия пользователя
        if ($user->getId() == null) {
            //Создание новой записи пользователя
            $user->setIdService($service->getId())
                 ->setIdServiceUser($idServiceUser)
                 //TODO: реализовать логику определения начального баланса пользователя
                 ->setBalance(500)
                 ->save();
            //Установка флага нового пользователя
            $this->_isNewUser = true;
        } else {
            //Выключение флага нового пользователя
            $this->_isNewUser = false;
        }

        //Возвращаем объект данных пользователя
        return $user;
    }

    /**
     * Проверка добавления нового пользователя в БД
     *
     * @return bool
     */
    public function isNewUser()
    {
        return $this->_isNewUser;
    }

    /**
     * Получение массива данных пользователя
     *
     * @param string $idServiceUser
     * @param string $nameService
     * @return array
     */
    public function getUserInfoArray($idServiceUser, $nameService)
    {
        //Получаем объект данных пользовталея
        $user = $this->getUserInfo($idServiceUser, $nameService);

        //Получение списка товаров пользователя и их сроков действия
        $shop = new App_Service_Server_Shop($idServiceUser, $nameService);
        $items = array();
        foreach($shop->getUserItems() as $item) {
            $items[$item['name']] = $item['deadline'];
        }

        //Получение данных таймеров пользователя
        $timer = new App_Service_Server_Timer();
        $timer->setUser($user);
        $timerData = $timer->getDataInArray();

        //Получение оповещений для пользователя
        $notifications = array();
        $notificationService = new App_Service_Server_Notification();
        foreach($notificationService->getNotifications($user->getId(), true) as $notification) {
            $notifications[] = array(
                'name' => $notification->getName(),
                'params' => $notification->getArrayParams()
            );
        }
        Zend_Registry::get('log')->debug(print_r($notifications, true));
        //Формирование массива данных пользователя
        return array(
            'uid' => $user->getId(),
            'id' => $user->getIdService(),
            'balance' => $user->getBalance(),
            'items' => $items,
            'flags' => $user->getFlags(),
            'timers' => $timerData,
            'notifications' => $notifications
        );
    }

    /**
     * Установка флага пользователю
     *
     * @param string $idServiceUser Идентификатор пользовтаеля в соц. сети
     * @param string $nameService Наименование соц. сети
     * @param $idFlag Порядковый номер флага (всего 5)
     * @param string|int|bool $flag Значение флага
     * @return bool
     * @throws Zend_Exception
     */
    public function setUserFlag($idServiceUser, $nameService, $idFlag, $flag = true)
    {
        //Конвертация флага в bool
        switch ($flag) {
            case 'true': $flag = true; break;
            case 'false': $flag = false; break;
        }
        //Получение объекта данных пользователя
        $user = $this->getUserInfo($idServiceUser, $nameService);
        //Установка флага
        return $this->switchUserFlag($user, $idFlag, $flag);
    }

    /**
     * Переключение состояния флага пользователя
     *
     * @param App_Model_User|string $user   Объект|идентификатор пользователя
     * @param int                   $idFlag Порядковый номер флага (всего 5)
     * @param string|int|bool       $flag   Значение флага
     * @return bool|int
     */
    public function switchUserFlag($user, $idFlag, $flag)
    {
        //Получение объекта данных пользователя
        if (is_string($user)) {
            $id = $user;
            $user = new App_Model_User();
            $user->find($id);
        }

        //Получаем список флагов пользователя в виде массива
        $arrFlags = explode(',', $user->getFlags());
        //установка флага
        $arrFlags[$idFlag - 1] = intval($flag);
        $user->setFlags(implode(',', $arrFlags));
        return $user->save();
    }

}
