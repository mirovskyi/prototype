<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 17.03.12
 * Time: 15:49
 *
 * Интерфейс оповещения клиента
 */
interface Core_Protocol_NotificationInterface
{

    /**
     * Клиент
     *
     * @abstract
     * @return mixed
     */
    public function client();

    /**
     * Выполнение оповещения
     *
     * @abstract
     * @return string
     */
    public function notify();

    /**
     * Проверка истечения срока оповещения
     *
     * @abstract
     * @return bool
     */
    public function isExpired();

    /**
     * Флаг передачи одного оповщения за раз
     *
     * @abstract
     * @return bool
     */
    public function isSingle();

    /**
     * Уничтожение оповещение (уведомление о закрытии)
     *
     * @abstract
     * @return void
     */
    public function destroy();

}
