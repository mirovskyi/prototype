<?php

return array(
    'usersession' => array(
        'required' => false,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//session[@type="user"]',
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
    
    'game' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//game',
            'fromAttribute' => 'id'
        )
    ),

    'quickstart' => array(
        'required' => false,
        'type' => 'boolean',
        'xml' => array(
            'xpath' => '//game',
            'fromAttribute' => 'quickstart'
        )
    )
);
 
