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

    'cards' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/cards'
        )
    ),

    'showcard' => array(
        'required' => false,
        'type' => 'bool',
        'xml' => array(
            'xpath' => '//params/cards',
            'fromAttribute' => 'show'
        )
    )
);