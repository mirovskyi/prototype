<?php

    return array(
        'usersession' => array(
            'required' => true,
            'type' => 'string',
            'xml' => array(
                'xpath' => '//session[@type="user"]'
            )
        ),

        'gamesession' => array(
            'required' => true,
            'type' => 'string',
            'xml' => array(
                'xpath' => '//session[@type="game"]'
            )
        ),

        'command' => array(
            'required' => true,
            'type' => 'string',
            'xml' => array(
                'xpath' => '//methodCall',
                'fromAttribute' => 'command'
            )
        ),

        'bet' => array(
            'required' => true,
            'type' => 'string',
            'xml' => array(
                'xpath' => '//bet'
            )
        ),

        'confirm' => array(
            'type' => 'bool',
            'xml' => array(
                'xpath' => '//confirm'
            )
        )
    );