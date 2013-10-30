<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 24.11.12
 * Time: 16:34
 *
 * Интерфейс формирования параметров пользователя соц. сети
 */
interface Core_Social_User_InfoInterface
{

    /**
     * Инициалиация параметров пользователя
     *
     * @abstract
     *
     * @param array $params  Параметры сесси пользователя соц. сети
     * @param array $configs Конфиги соц. сети
     */
    public function initUserInfo(array &$params, array $configs);

}
