<?php

return array(
    'usersession' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//session[@type="user"]'
        )
    ),
    
    'game' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/game',
            'fromAttribute' => 'id'
        )
    ),

    'chatId' => array(
        'type' => 'int',
        'xml' => array(
            'xpath' => '//methodCall',
            'fromAttribute' => 'chat'
        )
    )
);