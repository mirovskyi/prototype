<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 16.02.12
 * Time: 20:10
 * To change this template use File | Settings | File Templates.
 */
class Core_Server_Observer_Fault_ExceptionLog implements Core_Server_Observer_Interface
{

    public static function observe($observableSubject)
    {
        //Проверка полученного объекта
        if ($observableSubject instanceof Core_Protocol_Server_Fault) {
            //Проверка наличия ресурса логирования
            $bootstrap = Core_Server::getInstance()->getBootstrap();
            if ($bootstrap->hasResource('log')) {
                //Получение ресурса лога
                $log = $bootstrap->getResource('log')->bootstrap();
                //Логирование полученной ошибки
                $ex = $observableSubject->getException();
                $log->err($ex);
            }
        }
    }

}
