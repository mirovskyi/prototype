<?php

return array(
    'usersession' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//session[@type="user"]',
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

    'gamedata' => array(
        'required' => true,
        'type' => 'array',
        'validators' => array(
            'bet' => array('Int')
        ),
        'xml' => array(
            'xpath' => '//params/game'
        )
    )
);
