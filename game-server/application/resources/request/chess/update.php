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
            'xpath' => 'methodCall',
            'fromAttribute' => 'command'
        )
    ),

    'chatId' => array(
        'type' => 'int',
        'xml' => array(
            'xpath' => 'methodCall',
            'fromAttribute' => 'chat'
        )
    ),

    'pieceposition' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/move',
            'fromAttribute' => 'from'
        )
    ),

    'moveposition' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/move',
            'fromAttribute' => 'to'
        )
    ),

    'draw' => array(
        'required' => false,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/draw'
        )
    )
);