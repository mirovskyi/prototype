<?php

return array(
    'usersession' => array(
        'required' => false,
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

    'service' => array(
        'required' => false,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//service',
            'fromAttribute' => 'id'
        )
    ),

    'vars' => array(
        'required' => false,
        'type' => 'array',
        'xml' => array(
            'xpath' => '//var',
            'keyElem' => 'key',
            'valElem' => 'value'
        )
    ),

    'item' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/item',
            'fromAttribute' => 'id'
        )
    ),

    'money' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/item',
            'fromAttribute' => 'money'
        )
    ),
);