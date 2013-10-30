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

    'type' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//history',
            'fromAttribute' => 'type'
        )
    ),

    'date' => array(
        'required' => false,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//history/date'
        )
    ),

    'game' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//history/game',
            'fromAttribute' => 'id'
        )
    )
);