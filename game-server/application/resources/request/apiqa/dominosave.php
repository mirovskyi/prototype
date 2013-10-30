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

    'reserve' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/reserve'
        )
    ),

    'series' => array(
        'required' => true,
        'type' => 'string',
        'xml' => array(
            'xpath' => '//params/series'
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