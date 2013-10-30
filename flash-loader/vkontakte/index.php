<?php

    //Подключение файлов конфига
    include_once 'configs/filesys.php';
    include_once 'configs/server.php';

    //Подключение клиента JSON-RPC
    require_once 'libs/jsonclient.php';
    //Подключение слушателя событий
    require_once 'libs/listener.php';

    //Достаем параметры запроса
    $params = array();
    //Проверка наличия параметров запроса после символа #
    if (isset($_REQUEST['hash']) && $_REQUEST['hash'] != null) {
        $strParams = urldecode($_REQUEST['hash']);
        $arrParams = explode('&', $strParams);
        foreach($arrParams as $param) {
            $param = explode('=', $param);
            if (count($param) > 1) {
                $params[$param[0]] = $param[1];
            }
        }
    }
    $params = array_merge($_REQUEST, $params);

    //Создание объекта слушателя
    $listener = new Listener($params);
    //Обработка события
    try {
        $listener->handle();
    } catch (Exception $e) {
        syslog(1, $e);
    }

    //Отображение контента загрузчика флэша
    include 'loader.phtml';

