<?php

return array(
    'service' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//service',
            'fromAttribute' => 'id'
        )
    ),

    'vars' => array(
        'required' => true,
        'type' => 'array',
        'xml' => array(
            'xpath' => '//var',
            'keyElem' => 'key',
            'valElem' => 'value'
        )
    ),

    'date' => array(
        'required' => false,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//history/date'
        )
    ),

    'gameid' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//history/game',
            'fromAttribute' => 'sid'
        )
    )
);