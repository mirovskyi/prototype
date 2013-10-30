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
        'required' => false,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//session[@type="game"]'
        )
    ),

    'game' => array(
        'required' => false,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/game',
            'fromAttribute' => 'id'
        )
    ),

    'gamedata' => array(
        'required' => false,
        'type' => 'array',
        'xml' => array(
            'xpath' => '//params/game'
        )
    )
);
 
