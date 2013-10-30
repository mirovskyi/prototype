<?php

return array(
    
    'production' => array(
        
        'autoloadernamespaces' => array(
            'Core_'
        ),
        
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
                'server' => array(
                    'path' => 'server',
                    'namespace' => 'Server'
                ),
                'service' => array(
                    'path' => 'services',
                    'namespace' => 'Service'
                )
            )
        ),
        
        'resource' => array(
            
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
            
            'db' => array(
                'adapter' => 'pdo_mysql',
                'params' => array(
                    'host' => 'localhost',
                    'username' => 'root',
                    'password' => '',
                    'dbname' => 'game_balance'
                ),
                'isDefaultTableAdapter' => true,
                'defaultMetadataCache' => 'database'
            ),
            
            'log' => array(
                'filename' => APPLICATION_PATH . '/../data/logs/' . date('Y-m-d') . '.log',
                'isErrorHandler' => true,
                'isExceptionHandler' => true,
                'throwExceptions' => false
            )
            
        ),
        
        'config' => APPLICATION_PATH . '/configs/server/server.php'
        
    ),
    
    'development' => array(
        
        '_extend' => 'production'
        
    )
    
);