<?php

return array(
    
    'production' => array(
        
        'server' => array(

            //Формат данных протокола сервера
            //'format' => 'xml',

            //Путь к паттернам запросов
            'request' => array(
                'rulesDirectory' => APPLICATION_PATH . '/resources/request'
            ),

            //Класс ответа сервера
            'response' => array(
                'class' => 'Core_Protocol_Response_HttpXml'
            ),

            //Директория обработчиков запроса
            'handlerDirectory' => APPLICATION_PATH . '/controllers',

            //Установка доступных для обработки классов исключений
            //(если будет сгенерировано исключение, не входящее в список и не являющееся потомком классов исключений в списке,
            //в ответе сервера будет передано стандартное сообщение об ошибке 500)
            'avaliableExceptions' => array(
                'Exception'
            ),

            //Регистация классов слушателей возникновения ошибки,
            //в параметре объекта слушателя передается объект ошибки Core_Protocol_Server_Fault
            'faultObservers' => array(
                'Core_Server_Observer_Fault_ExceptionLog'
            ),
        )
        
    ),
    
    'development' => array(
        
        '_extend' => 'production',
        
    )
    
);