<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 21.03.12
 * Time: 11:28
 *
 * Декоратор ответа сервера. Добавление списка оповещений для пользователя в конец тела ответа
 */
class App_Service_View_Decorator_Notification extends Core_View_Decorator_Abstract
{

    /**
     * Получение контента шаблона
     *
     * @throws Core_View_Exception
     * @param string|null $template
     * @return string
     */
    protected function _render($template = null)
    {
        //Блок оповещений
        $notificationsElement = '';

        //Список типов оповещений
        $notifications = array();

        //Проверка наличия данных сессий пользователя в реестре
        if (Core_Session::getInstance()->has(Core_Session::USER_NAMESPACE)) {
            //Получаем данные сессии пользователя
            $session = $this->_getUserSession();
            //Формирование блока оповещений
            foreach($session->getNotifications() as $notificationKey) {
                $notification = $this->_getNotification($notificationKey);
                if ($notification instanceof Core_Protocol_NotificationInterface) {
                    //Проверяем флаг одного оповещения за раз
                    if ($notification->isSingle()) {
                        //Проверяем были ли подобные оповещения
                        if (in_array(get_class($notification), $notifications)) {
                            //Оповещения подобного типа уже добавлены
                            continue;
                        }
                    }
                    //Добавляем тип оповещения в список
                    $notifications[] = get_class($notification);
                    //Устанавливаем контент оповещения
                    $notificationsElement .= $notification->notify();
                }
            }
        }
        //Достаем объекты оповещения из шаблона ответа
        if (isset($this->notification)) {
            foreach($this->get('notification') as $notification) {
                if ($notification instanceof Core_Protocol_NotificationInterface) {
                    //Проверяем флаг одного оповещения за раз
                    if ($notification->isSingle()) {
                        //Проверяем были ли подобные оповещения
                        if (in_array(get_class($notification), $notifications)) {
                            //Оповещения подобного типа уже добавлены
                            continue;
                        }
                    }
                    //Добавляем тип оповещения в список
                    $notifications[] = get_class($notification);
                    //Устанавливаем контент оповещения
                    $notificationsElement .= $notification->notify();
                }
            }
        }

        //Оборачиваем в блок <notification>
        $notificationsElement = '<notification>' . $notificationsElement . '</notification>';

        //Получаем ответ сервера, добавляем в конец блок оповещений
        $response = parent::_render($template) . $notificationsElement;
        //Возвращаем ответ сервера
        return $response;
    }

    /**
     * Получение объекта сессии пользователя
     *
     * @return App_Model_Session_User
     */
    private function _getUserSession()
    {
        return Core_Session::getInstance()->get(Core_Session::USER_NAMESPACE);
    }

    /**
     * Получение объекта оповещения
     *
     * @param string $key Уникальный ключ оповещения
     *
     * @return Core_Protocol_NotificationInterface|bool
     */
    private function _getNotification($key)
    {
        return Core_Storage::factory()->get($key);
    }

}
