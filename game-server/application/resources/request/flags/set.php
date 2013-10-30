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
            'xpath' => '//service/vars/var',
            'keyElem' => 'key',
            'valElem' => 'value'
        )
    ),

    'id' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/flag',
            'fromAttribute' => 'id'
        )
    ),

    'value' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/flag',
            'fromAttribute' => 'set'
        )
    ),
);