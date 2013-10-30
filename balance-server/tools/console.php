<?php

error_reporting(E_ALL);

//Путь к текущей директории
defined('CONSOLE_PATH') | 
    define('CONSOLE_PATH', realpath(dirname(__FILE__)));

//Окружение
defined('CONSOLE_ENV') | 
    define('CONSOLE_ENV', 'console');

//Подключаем библиотеки
set_include_path(get_include_path() . PATH_SEPARATOR 
                 . CONSOLE_PATH . PATH_SEPARATOR 
                 . CONSOLE_PATH . '/../www/library');

//Подключаем Autoloader
require_once 'Console/Core/Loader.php';

//Регистрация автозагрузчика
Console_Core_Loader::registerAutoload();

//Bootstrap
$bootstrap = new Console_Bootstrap(CONSOLE_PATH . '/configs/console.yml');
$bootstrap->start();