<?php

error_reporting(E_ALL);

function timeMeasure()
{
    list($msec, $sec) = explode(chr(32), microtime());
    return ($sec+$msec);
}
define('TIMESTART', timeMeasure());

    //Определение пути к директории приложения
    defined('APPLICATION_PATH') ||
        define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

    //Определение окружения
    defined('APPLICATION_ENV') ||
        define('APPLICATION_ENV', getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production');
        
    //Добавление директории библиотек приложения в include_path
    set_include_path(implode(PATH_SEPARATOR, array(
        realpath(APPLICATION_PATH . '/../library'),
        get_include_path()
    )));

    //Подключаем класс сервера
    require_once 'Core/Server.php';
    $server = Core_Server::getInstance();
    $server->init(
        APPLICATION_ENV,
        APPLICATION_PATH . '/configs/server/config.php'
    );
    
    //Запуск сервера
    $server->run();