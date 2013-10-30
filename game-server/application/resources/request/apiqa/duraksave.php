<?php

return array(

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

    'pack' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/pack'
        )
    ),

    'pulldown' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/pulldown'
        )
    ),

    'players' => array(
        'required' => true,
        'type' => 'array',
        'xml' => array(
            'xpath' => '//params/players/player'
        )
    )
);