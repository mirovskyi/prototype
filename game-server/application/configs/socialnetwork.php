<?php

return array(


    //Конфиги соц. сети "Вконтакте"
    'vkontakte' => array(

        //Правила преобразования данных пользователя из соц. сети в объект пользователя в системе,
        //ключ - название своиства объекта Core_Social_User, значение - параметр пользователя в соц. сети
        'user' => array(
            'id' => 'viewer_id',
            'name' => array('first_name', 'last_name'),
            'photo' => 'photo'
        )

    ),

    //Конфиги соц. сети "Мой Мир" Mail.ru
    'mailru' => array(

        'apiServer' => 'http://www.appsmail.ru/platform/api',
        'secretKey' => '4947139c955a8e918ad1380717f3ad2e',
        //Правила преобразования данных пользователя из соц. сети в объект пользователя в системе,
        //ключ - название своиства объекта Core_Social_User, значение - параметр пользователя в соц. сети
        'user' => array(
            '_init' => 'Core_Social_User_Mailru',
            'id' => 'vid',
            'name' => 'name',
            'photo' => 'photo'
        ),
    ),

    //Конфиги соц. сети odnoklasniki.ru
    'odnoklasniki' => array(

        //Правила преобразования данных пользователя из соц. сети в объект пользователя в системе,
        //ключ - название своиства объекта Core_Social_User, значение - параметр пользователя в соц. сети
        'user' => array(
            '_init' => 'Core_Social_User_Odnoklassniki',
            'id' => 'logged_user_id',
            'name' => 'name',
            'photo' => 'photo'
        )
    ),

    //Конфиги флэша, интегрированного через HTML, без соц. сети
    'igrok' => array(
        //Правила преобразования данных пользователя из соц. сети в объект пользователя в системе,
        //ключ - название своиства объекта Core_Social_User, значение - параметр пользователя в соц. сети
        'user' => array(
            'id' => 'id',
            'name' => 'login',
        )
    ),

    //Имя платформы интегрированной через html, вне соц. сети
    'htmlServiceName' => 'igrok'
    
);
