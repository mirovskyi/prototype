<?php

return array(

    'cli' => array(

        'cli' => array(
            'appnamespace' => array(
                'basePath' => CLI_PATH,
                'namespace' => 'Cli',
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
                    'service' => array(
                        'path' => 'services',
                        'namespace' => 'Service'
                    )
                )
            ),

            'controllerDirectory' => CLI_PATH . '/controllers'
        )

    )

);
