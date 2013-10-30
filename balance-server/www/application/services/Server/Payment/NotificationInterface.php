<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 31.08.12
 * Time: 13:32
 *
 * Интерфейс обработчика оповещения о результате платежа
 */
interface App_Service_Server_Payment_NotificationInterface
{

    /**
     * Установка параметров запроса оповещения
     *
     * @abstract
     *
     * @param array $params Массив параметров запроса
     *
     * @return void
     */
    public function setRequestParams(array $params);

    /**
     * Инициализвция платежа
     *
     * @return mixed
     */
    public function init();

    /**
     * Обработка оповещения об успешной оплате платежа
     *
     * @abstract
     * @return App_Model_Payment|bool
     */
    public function success();

    /**
     * Флаг дублирующего запроса успешности платежа
     *
     * @abstract
     * @return bool
     */
    public function isRepeat();

}
