#!/usr/local/bin/php
<?php

//Определение пути к директории приложений
defined('APPLICATION_PATH') ||
    define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('CLI_PATH') ||
    define('CLI_PATH', realpath(dirname(__FILE__)));

//Определение окружения
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'cli'));

//Добавление директории библиотек приложения в include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path()
)));

//Подключаем библиотеку настройки сервера
require_once 'Core/Cli.php';

//Запуск консольного приложения
$cli = new Core_Cli(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/config.php'
);
$cli->run();
