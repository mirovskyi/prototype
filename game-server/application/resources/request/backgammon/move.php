<?php

return array(
    'usersession' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//session[@type="user"]'
        )
    ),

    'gamesession' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//session[@type="game"]'
        )
    ),

    'command' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//methodCall',
            'fromAttribute' => 'command'
        )
    ),

    'chatId' => array(
        'type' => 'int',
        'xml' => array(
            'xpath' => '//methodCall',
            'fromAttribute' => 'chat'
        )
    ),

    'from' => array(
        'required' => true,
        'type' => 'int',
        'xml' => array(
            'xpath' => '//params/position',
            'fromAttribute' => 'from'
        )
    ),

    'to' => array(
        'required' => true,
        'type' => 'int',
        'xml' => array(
            'xpath' => '//params/position',
            'fromAttribute' => 'to'
        )
    )
);