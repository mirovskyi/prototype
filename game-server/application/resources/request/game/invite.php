<?php

return array(
    'usersession' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//session[@type="user"]',
        )
    ),

    'gamesession' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//session[@type="game"]',
        )
    ),

    'users' => array(
        'required' => true,
        'type' => 'array',
        'xml' => array(
            'xpath' => '//users/sid'
        )
    )
);
