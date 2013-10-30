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

    'filterName' => array(
        'required' => false,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//filter/item',
            'fromAttribute' => 'name'
        )
    )
);