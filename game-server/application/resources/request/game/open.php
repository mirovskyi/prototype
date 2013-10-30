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
            'xpath' => '//game',
            'fromAttribute' => 'sid'
        )
    ),

    'game' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//game',
            'fromAttribute' => 'id'
        )
    )
);