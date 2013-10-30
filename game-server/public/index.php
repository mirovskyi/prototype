<?php

error_reporting(E_ALL);

function timeMeasure()
{
    list($msec, $sec) = explode(chr(32), microtime());
    return ($sec+$msec);
}

define('TIMESTART', timeMeasure());

//Определение пути к директории приложений
defined('APPLICATION_PATH') ||
    define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

//Определение окружения
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

//Добавление директории библиотек приложения в include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path()
)));

//Подключаем библиотеку настройки сервера
require_once 'Core/Server.php';

//Запуск сервера
$server = Core_Server::getInstance()->init(
    APPLICATION_ENV, 
    APPLICATION_PATH . '/configs/config.php'
);

$server->run();