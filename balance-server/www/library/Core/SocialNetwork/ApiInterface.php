<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 02.10.12
 * Time: 18:23
 *
 * Интерфейс реализации API социальной сети
 */
interface Core_SocialNetwork_ApiInterface
{

    /**
     * Получение имени пользователя в соц. сети
     *
     * @param string $id Идентификатор пользователя в соц. сети
     * @return string
     */
    public function getUserName($id);

}
