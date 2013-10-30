<?php

return array(
    'usersession' => array(
        'required' => false,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//session[@type="user"]'
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

    'transId' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//payment/transId'
        )
    ),

    'paymentId' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//payment/productId'
        )
    )
);