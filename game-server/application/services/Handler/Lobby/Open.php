<?php

 
class App_Service_Handler_Lobby_Open extends App_Service_Handler_Abstract
{

    /**
     * Открытие страницы лобби
     *
     * @return string
     */
    public function handle()
    {
        //Получение имени сервиса социальной сети
        $service = $this->getRequest()->get('service');
        //Получение объекта пользователя соц. сети
        $socialUser = new Core_Social_User($service, $this->getRequest()->get('vars'));
        //Сохраняем URL адрес фотографии пользователя в хранилище
        $this->_saveUserPhotoUrl($socialUser);
        //Получаем данные пользовтаеля
        $info = $this->_getUserInfo($socialUser->getId(), $service);
        //Обработка (создание) оповещений для пользователя
        if (isset($info['notifications'])) {
            $this->_createNotifications($info['notifications']);
        }

        //Формирование данных ответа
        $response = array(
            'idUser' => $socialUser->getId(),
            'nameUser' => $socialUser->getName(),
            'balanceUser' => $info['balance'],
            'flags' => $info['flags'],
            'timers' => $info['timers'],
            'photoUser' => $socialUser->getPhotoUrl(),
            'games' => $this->_getGames()
        );

        //Возвращаем контент ответа
        $this->view->assign($response);
        return $this->view->render();
    }

    /**
     * Получение данных пользователя
     *
     * @param string $idServiceUser
     * @param string $service
     * @return array
     */
    protected function _getUserInfo($idServiceUser, $service)
    {
        $balance = new Core_Api_DataService_Info();
        return $balance->getUserInfo($idServiceUser, $service);
    }

    /**
     * Получение списка игр
     *
     * @return array
     */
    protected function _getGames()
    {
        $info = new Core_Api_DataService_Info();
        return $info->getGames();
    }

    /**
     * Сохранение URL фотографии пользователя в хранилище
     *
     * @param Core_Social_User $userInfo
     */
    protected function _saveUserPhotoUrl(Core_Social_User $userInfo)
    {
        //URL фото пользователя в соц. сети
        $photo = $userInfo->getPhoto();
        //Проверка URL адреса
        if (null != $photo) {
            //Сохраняем URL в хранилище
            $key = $userInfo->getPhotoKey();
            Core_Storage::factory()->set($key, $photo);
        }
    }

    /**
     * Создание оповещений пользователя
     *
     * @param array $notifications Массив данных оповещений
     */
    protected function _createNotifications($notifications)
    {
        if (!is_array($notifications) || !count($notifications)) {
            return;
        }

        //Создание оповещений
        foreach($notifications as $data) {
            if (!isset($data['name'])) {
                continue;
            }
            //Поиск соответствующего класса оповещения
            $className = 'App_Model_Lobby_Notification_' . ucfirst($data['name']);
            if (!class_exists($className)) {
                continue;
            }
            //Параметры оповещения
            $params = isset($data['params']) ? $data['params'] : array();
            //Создание объекта оповещения
            $notification = new $className($params);
            //Передача оповещения в шаблон вида
            if ($notification instanceof Core_Protocol_NotificationInterface) {
                //Достаем массив оповещений в шаблоне ответа
                $viewNotifications = $this->view->get('notification', array());
                //Дописываем в массив текущее оповещение
                $viewNotifications[] = $notification;
                //Обновляем массив оповещений в  шаблоне ответа
                $this->view->assign('notification', $viewNotifications);
            }
        }
    }

}
