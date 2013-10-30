<?php

return array(
    'usersession' => array(
        'required' => false,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//session[@type="user"]',
        )
    ),

    'data' => array(
        'required' => true,
        'type' => 'array',
        'xml' => array(
            'xpath' => '//params/user',
            'attributes' => 'all'
        )
    ),
);