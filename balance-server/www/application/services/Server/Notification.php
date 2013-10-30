<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 02.10.12
 * Time: 16:42
 *
 * Сервис работы с оповещениями пользователей
 */
class App_Service_Server_Notification
{

    /**
     * Добавление оповещения пользователя
     *
     * @param int    $userId Идентификатор пользователя в БД
     * @param string $name   Наименование оповещения
     * @param array  $params Параметры оповещения
     *
     * @return bool
     */
    public function addNotification($userId, $name, array $params = null)
    {
        //Создание объекта оповещения
        $notification = new App_Model_Notification();
        $notification->setIdUser($userId);
        $notification->setName($name);
        if (null !== $params) {
            $notification->setArrayParams($params);
        }
        if ($notification->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получение списка объектов оповещений пользователя
     *
     * @param int  $userId  Идентификатор пользователя в БД
     * @param bool $delete  Флаг необходимости удаления полученных оповещений из БД
     * @return App_Model_Notification[]
     */
    public function getNotifications($userId, $delete = false)
    {
        //Объект модели данных оповещения
        $notification = new App_Model_Notification();
        //Поиск всех оповещений пользователя
        $where = $notification->select()->where('id_user = ?', $userId);
        $notifications = $notification->fetchAll($where);

        //Проверка необходимости удаления оповещений
        if ($delete && count($notifications)) {
            $where = 'id IN (' . implode(',', $notifications) . ')';
            $notification->delete($where);
        }

        //Возвращаем список объектов оповещений
        return $notifications;
    }

}
