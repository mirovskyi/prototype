<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 10.04.12
 * Time: 14:56
 * To change this template use File | Settings | File Templates.
 */
class Core_Plugin_HighLoad extends Core_Plugin_Abstract
{

    public function preHandle()
    {
        $storage = Core_Storage::factory();
        $current = intval($storage->get('request:count'));
        //Задержка каждый 5 запрос
        if ($current % 5 == 0) {
            sleep(rand() % 5);
        }
        //Инкремент порядкового номера запроса
        $current ++;
        $storage->set('request:count', $current);
    }

}
