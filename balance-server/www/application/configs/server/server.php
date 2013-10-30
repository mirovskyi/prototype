<?php

return array(
    
    'production' => array(
        
        'server' => array(
            'cache' => APPLICATION_PATH . '/../data/cache/server/reflection.cache',
            'namespaces' => array(
                'balance' => array(
                    'App_Server_Balance'
                ),
                'shop' => array(
                    'App_Server_Shop'
                ),
                'experience' => array(
                    'App_Server_Experience'
                ),
                'payment' => array(
                    'App_Server_Payment'
                ),
                'bonus' => array(
                    'App_Server_Bonus'
                ),
                'user' => array(
                    'App_Server_User'
                ),
                'gift' => array(
                    'App_Server_Gift'
                ),

                'App_Server_Info'
            )
        ),

        'application' => array(
            'vkontakte' => array(
                'url' => 'http://api.vk.com/api.php',
                'appId' => '2914923',
                'secretKey' => '05e31eee39d6b28f1bebcabcf9781fbf'
            ),
            'mailru' => array(
                'url' => 'http://api.vk.com/api.php',
                'appId' => '2914923',
                'secretKey' => '4947139c955a8e918ad1380717f3ad2e'
            ),
            'odnoklassniki' => array(
                'url' => 'http://api.vk.com/api.php',
                'appId' => '2914923',
                'secretKey' => '5C3B100ADB0113B02586CEA8'
            )
        )
        
    ),
    
    'development' => array(
        
        '_extend' => 'production',
        
        'server' => array(
            'cache' => false
        )
        
    )
    
);