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
        'required' => true,
        'type' => 'int',
        'xml' => array(
            'xpath' => '//methodCall',
            'fromAttribute' => 'chat'
        )
    ),

    'text' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/text'
        )
    ),

    'recipient' => array(
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/recipient'
        )
    )
);