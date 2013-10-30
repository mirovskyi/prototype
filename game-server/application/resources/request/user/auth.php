<?php

return array(
    'login' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/user',
            'fromAttribute' => 'login'
        )
    ),

    'passwd' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/user',
            'fromAttribute' => 'passwd'
        )
    ),
);