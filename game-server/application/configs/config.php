<?php

return array(
    
    'production' => array(

        //Регистрация пространства имени классов
        'autoloadernamespaces' => array(
            'Core_'
        ),

        //Структура пространста имен приложения
        'appnamespace' => array(
            'basePath' => APPLICATION_PATH,
            'namespace' => 'App',
            'resourceTypes' => array(
                'model' => array(
                    'path' => 'models/',
                    'namespace' => 'Model'
                ),
                'dbtable' => array(
                    'path' => 'models/DbTable',
                    'namespace' => 'Model_DbTable'
                ),
                'mapper' => array(
                    'path' => 'models/mappers',
                    'namespace' => 'Model_Mapper',
                ),
                'namespace' => array(
                    'path' => 'namespaces',
                    'namespace' => 'Namespace'
                ),
                'service' => array(
                    'path' => 'services',
                    'namespace' => 'Service'
                )
            )
        ),

        //Установка плагинов
        'plugins' => array(
            'userPhotoRequest' => 'Core_Plugin_UserPhoto',
            'photoProxy' => 'Core_Plugin_PhotoProxy',
            'debug' => 'Core_Plugin_Log',
            //'highLoad' => 'Core_Plugin_HighLoad',
        ),

        //Установка ресурсов приложения
        'resource' => array(

             //Настройки ресурса логирования
            'log' => array(
                'filename' => APPLICATION_PATH . '/../data/logs/' . date('Y-m-d') . '.log',
                'isErrorHandler' => true,
                'isExceptionHandler' => true,
                'throwExceptions' => true
            ),

            //Менеджер кэша
            'cachemanager' => array(
                'database' => array(
                    'frontend' => array(
                        'name' => 'Core',
                        'customFrontendNaming' => false,
                        'options' => array(
                            'lifetime' => 7200,
                            'automatic_serialization' => true
                        )
                    ),
                    'backend' => array(
                        'name' => 'File',
                        'customBackendNaming' => false,
                        'options' => array(
                            'cache_dir' => APPLICATION_PATH . '/../data/cache/database'
                        )
                    ),
                    'frontendBackendAutoload' => false
                ),

            ),

            //Соединение с БД
            'db' => array(
                'adapter' => 'pdo_mysql',
                'params' => array(
                    'host' => 'localhost',
                    'username' => 'root',
                    'password' => '',
                    'dbname' => 'game_history'
                ),
                'isDefaultTableAdapter' => true,
                //'defaultMetadataCache' => 'database'
            ),

            //Настройки соединения с Memcache
            'memcached' => array(
                'servers' => array(
                    'localhost' => array(
                        'host' => '127.0.0.1'
                    )
                )
            )
            
        ),

        //Настройка реализации сохранения истории игр
        'historyDb' => array(
            'name' => 'relational',
            'params' => array(
                'cached' => true
            )
        ),

        //Настройка объекта шаблонов ответа сервера
        'view' => array(
            //Формат ответа сервера (в зависимости от значения подгружается соответствующий класс вида Core_View)
            'format' => 'xml',
            //Директория шаблонов ответа сервера
            'templateDirectory' => APPLICATION_PATH . '/resources/response',
            //Деораторы (wrappers) ответа сервера
            'decorators' => array(
                'App_Service_View_Decorator_MethodCall',
                'App_Service_View_Decorator_Notification'
            ),
            //Помошники действий
            'helper' => array(
                'App_Service_View_Helper' => APPLICATION_PATH . '/services/View/Helper'
            )
        ),
        
        //Дополнительные конфиги системы
        'config' => array(
            APPLICATION_PATH . '/configs/server.php',
            APPLICATION_PATH . '/configs/api.php'
        ),
        
        //Путь к файлу конфигов социальных сетей
        'socialnetwork_config' => APPLICATION_PATH . '/configs/socialnetwork.php'
        
    ),
    
    'development' => array(
        
        '_extend' => 'production',

        'resource' => array(
            'db' => array(
                'adapter' => 'pdo_mysql',
                'params' => array(
                    'host' => 'localhost',
                    'username' => 'root',
                    'password' => '',
                    'dbname' => 'game'
                ),
                'isDefaultTableAdapter' => true,
                'defaultMetadataCache' => 'database'
            ),
        )
        
    ),

    'cli' => array (

        '_extend' => 'production',

        'resource' => array(
            'log' => array(
                'filename' => APPLICATION_PATH . '/../data/logs/cron/' . date('Y-m-d') . '.log',
                'isErrorHandler' => true,
                'throwExceptions' => true
            )
        ),

        //Дополнительные конфиги системы
        'config' => array(
            APPLICATION_PATH . '/configs/cli.php',
            APPLICATION_PATH . '/configs/api.php'
        )
    ),

    'cli_dev' => array (

        '_extend' => 'development',

        'resource' => array(
            'log' => array(
                'filename' => APPLICATION_PATH . '/../data/logs/cron/' . date('Y-m-d') . '.log',
                'isErrorHandler' => true,
                'throwExceptions' => true
            )
        ),

        //Дополнительные конфиги системы
        'config' => array(
            APPLICATION_PATH . '/configs/cli.php',
            APPLICATION_PATH . '/configs/api.php'
        )
    )
    
);
