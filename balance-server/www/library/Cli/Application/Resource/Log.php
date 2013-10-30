<?php

class Cli_Application_Resource_Log extends Zend_Application_Resource_ResourceAbstract
{
    
    /**
     * Наименование ключа лога, зарегистрированного для обработчика ошибок
     * @var string
     */
    public static $errorHandlerName = '';

    /**
     * Наименование ключа лога, зарегистрированного для обработчика исключений
     * @var string
     */
    public static $exceptionHandlerName = '';
    
    /**
     * Метод инициализации ресурса
     */
    public function init()
    {
        //Получение, обработка конфига логов
        $options = $this->getOptions();
        //Создание и регистрация логов в реестре
        if (APPLICATION_ENV == 'development') {
            $this->_registerDevLogs($options);
        } else {
            $this->_registerLogs($options);
        }
    }
    
    /**
     * Метод обработчика ошибок
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @return void 
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        //Проверка наличия лога в регистре
        if (!Zend_Registry::isRegistered(self::$errorHandlerName)) {
            return false;
        }
        //Тип ошибки
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT: $type = Zend_Log::NOTICE; break;
            case E_CORE_WARNING:
            case E_USER_WARNING:
            case E_WARNING: $type = Zend_Log::WARN; break;
            case E_COMPILE_ERROR:
            case E_CORE_ERROR:
            case E_ERROR: $type = Zend_Log::CRIT; break;
            case E_USER_ERROR: $type = Zend_Log::ERR; break;
            default: $type = Zend_Log::INFO;
        }
        //Формирование сообщения
        $message = $errstr . ' [FILE: ' . $errfile . '; LINE: ' . $errline . ']';
        //Логирование ошибки
        Zend_Registry::get(self::$errorHandlerName)->log($message, $type);
    }

    /**
     * Обработчик исключений
     *
     * @param Exception $exception
     * @return bool
     */
    public function exceptionHandler(Exception $exception)
    {
        //Проверка наличия лога в регистре
        if (!Zend_Registry::isRegistered(self::$exceptionHandlerName)) {
            return false;
        }
        //Логирование ошибки
        Zend_Registry::get(self::$exceptionHandlerName)->err($exception);
    }
    
    /**
     * Метод создания и регистрации логов
     * @param array $options 
     */
    protected function _registerLogs($options)
    {
        //Создание и регистрация логов в реестре
        foreach($options as $name => $configure) {
            if (is_array($configure) && isset($configure['path'])) {
                //Формируем путь к файлу лога
                $stream = $configure['path'] . '/' . date('Y-m-d') . '.log';
                //Создание лога
                $writer = new Zend_Log_Writer_Stream($stream);
                $logger = new Zend_Log($writer);
                //Регистрация лога в реестре
                Zend_Registry::set($name, $logger);
                //Проверка флага использование логов для записи сис. ошибок
                if (isset($configure['systemErrorHandle']) &&
                    $configure['systemErrorHandle'] == true) {
                    //Сохранение ключа лога для обработчика
                    self::$errorHandlerName  = $name;
                    //Регистрируем объект лога для использования в обработчике
                    set_error_handler(array($this, 'errorHandler'));
                }
                //Проверка флага использование логов для записи неперехваченых исключений
                if (isset($configure['exceptionHandle']) &&
                    $configure['exceptionHandle'] == true) {
                    //Сохранение ключа лога для обработчика
                    self::$exceptionHandlerName  = $name;
                    //Регистрируем объект лога для использования в обработчике
                    set_error_handler(array($this, 'exceptionHandler'));
                }
            }
        }
    }
    
    /**
     * Метод создания и регистрации объектов эмитации логов (для режима development)
     * Текст логов выводидтся на экран
     * @param array $options 
     */
    protected function _registerDevLogs($options)
    {
        //Создание и регистрация логов в реестре
        foreach($options as $name => $configure) {
            //Создание объекта лога для dev режима (вывод сообщений на экран)
            $logger = new Cli_Log();
            Zend_Registry::set($name, $logger);
        }
    }
    
}