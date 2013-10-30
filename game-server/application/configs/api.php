<?php

return array(
    
    'production' => array(

        'api' => array(

            'dataservice' => array(
                'url' => 'http://socialserv.sendmailserver2.ru/balance.php',
                'log' => array(
                    'filename' => APPLICATION_PATH . '/../data/logs/api/' . date('Y-m-d') . '.log'
                ),
                'debug' => true,

                'balance' => array(
                    'namespace' => 'balance',
                ),

                'shop' => array(
                    'namespace' => 'shop',
                ),

                'payment' => array(
                    'namespace' => 'payment',
                ),

                'experience' => array(
                    'namespace' => 'experience',
                ),

                'bonus' => array(
                    'namespace' => 'bonus',
                ),

                'user' => array(
                    'namespace' => 'user',
                ),

                'gift' => array(
                    'namespace' => 'gift'
                ),
            )

        )

    ),


    'development' => array(
        '_extend' => 'production',

        /*'api' => array(

            'dataservice' => array(
                'url' => 'http://localhost/game_balance/trunk/www/public/server.php',
            )

        )*/
    ),


    'cli' => array(
        '_extend' => 'production',
    ),


    'cli_dev' => array(
        '_extend' => 'development',
    )

);