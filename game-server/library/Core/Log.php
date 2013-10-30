<?php

/**
 * Логирование
 *
 * @author aleksey
 */
class Core_Log 
{
    
    //Приоритеты
    const EMERG   = 0;
    const ALERT   = 1;
    const CRIT    = 2;
    const ERR     = 3;
    const WARN    = 4;
    const NOTICE  = 5;
    const INFO    = 6;
    const DEBUG   = 7;

    //Формат записи лога по умолчанию
    const DEFAULT_FORMAT = '%date% %priority%: %message% [%file%, %line%]';

    /**
     * Массив стандартных типов php ошибок
     *
     * @var array
     */
    protected $_errorHandlerMap = array();
    
    /**
     * Путь к файту лога
     *
     * @var string
     */
    protected $_filename;
    
    /**
     * Формат записи лога
     *
     * @var string
     */
    protected $_format;
    
    /**
     * Формат времено в записи лога
     *
     * @var string 
     */
    protected $_timeFormat = 'c';
    
    /**
     * Флаг выбрасывания исключения при ошибки записи лога
     *
     * @var boolean
     */
    protected $_throwExceptions = false;
    
    /**
     * Приоритеты ошибок
     *
     * @var array
     */
    protected $_priorities = array();
    
    /**
     * Флаг регистрации лога как обработчика ошибок
     *
     * @var boolean
     */
    protected $_registeredErrorHandler = false;

    /**
     * Флаг регистрации лога как обработчика исключений
     *
     * @var bool
     */
    protected $_registeredExceptionHandler = false;
    
    /**
     * Ссылка обработчика ошибок
     *
     * @var mixed
     */
    protected $_origErrorHandler;

    /**
     * Ссылка на обработчик исключений
     *
     * @var mixed
     */
    protected $_origExceptionHandler;
    
    
    /**
     * __construct
     *
     * @param array|null $options Настройки логирования
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }

        $r = new ReflectionClass($this);
        $this->_priorities = array_flip($r->getConstants());
    }

    /**
     * Установка настроек логирования
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        //Установка пути к файлу лога
        if (isset($options['filename'])) {
            $this->setFilename($options['filename']);
        }
        //Установка формата записи лога
        if (isset($options['format'])) {
            $this->setFormat($options['format']);
        }
    }
    
    /**
     * Установка пути к файлу лога
     *
     * @param string $filename
     * @return Core_Log 
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;
        return $this;
    }
    
    /**
     * Получение пути к файлу лога
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }
    
    /**
     * Установка формата записи лога
     *
     * @param string $format
     * @return Core_Log 
     */
    public function setFormat($format)
    {
        $this->_format = $format;
        return $this;
    }
    
    /**
     * Получение формата записи лога
     *
     * @return string 
     */
    public function getFormat()
    {
        if (null === $this->_format) {
            $this->setFormat(self::DEFAULT_FORMAT);
        }
        return $this->_format;
    }
    
    /**
     * Установка формата времени
     *
     * @param string $timeFormat
     * @return Core_Log 
     */
    public function setTimeFormat($timeFormat)
    {
        $this->_timeFormat = $timeFormat;
        return $this;
    }
    
    /**
     * Получение формата времени
     *
     * @return string
     */
    public function getTimeFormat()
    {
        return $this->_timeFormat;
    }
    
    /**
     * Установка флага выбрасывания исключений
     *
     * @param boolean $throw 
     */
    public function setThrowException($throw = true)
    {
        $this->_throwExceptions = $throw;
    }
    
    /**
     * Проверка необходимости выбрасывания исключения при возникновении ошибки
     *
     * @return boolean 
     */
    public function isThrowException()
    {
        return $this->_throwExceptions;
    }

    /**
     * Проверка наличия зарегистрированного обработчика ошибок
     *
     * @return bool
     */
    public function isRegisteredErrorHandle()
    {
        return $this->_registeredErrorHandler;
    }

    /**
     * Проверка наличия зарегистрированного обработчика исключений
     *
     * @return bool
     */
    public function isRegisteredExceptionHandler()
    {
        return $this->_registeredExceptionHandler;
    }
    
    /**
     * __call
     *
     * @param string $name
     * @param array $arguments
     * @return mixed 
     */
    public function __call($name, $arguments) 
    {
        $priority = strtoupper($name);
        if (($priority = array_search($priority, $this->_priorities)) !== false) {
            switch (count($arguments)) {
                case 0: 
                    if ($this->_throwExceptions) {
                        throw new Core_Exception('Missing log message');
                    } else {
                        return false;
                    }
                    break;
                case 1:
                    $message = array_shift($arguments);
                    $file = null;
                    $line = null;
                    break;
                case 2:
                    $message = array_shift($arguments);
                    $file = array_shift($arguments);
                    $line = null;
                    break;
                default:
                    $message = array_shift($arguments);
                    $file = array_shift($arguments);
                    $line = array_shift($arguments);
                    break;
            }
            return $this->log($message, $priority, $file, $line);
        }
        return false;
    }
    
    /**
     * Запись лога
     *
     * @param string $message
     * @param integer $priority
     * @param string $file
     * @param integer $line
     * @return boolean 
     */
    public function log($message, $priority, $file = null, $line = null)
    {
        $log = $this->_formatter($message, $priority, $file, $line) . PHP_EOL;
        $result = @file_put_contents($this->getFilename(), $log, FILE_APPEND);
        if (!$result) {
            if ($this->isThrowException()) {
                throw new Core_Exception('Log file "' . $this->getFilename() 
                                         . '" is not writable');
            }
        }
        return $result;
    }
    
    /**
     * Регистрация лога как обработчика ошибок
     *
     * @return Core_Log 
     */
    public function registerErrorHandler()
    {
        // Only register once.  Avoids loop issues if it gets registered twice.
        if ($this->_registeredErrorHandler) {
            return $this;
        }

        $this->_origErrorHandler = set_error_handler(array($this, 'errorHandler'));

        // Contruct a default map of phpErrors to Zend_Log priorities.
        // Some of the errors are uncatchable, but are included for completeness
        $this->_errorHandlerMap = array(
            E_NOTICE            => self::NOTICE,
            E_USER_NOTICE       => self::NOTICE,
            E_WARNING           => self::WARN,
            E_CORE_WARNING      => self::WARN,
            E_USER_WARNING      => self::WARN,
            E_ERROR             => self::ERR,
            E_USER_ERROR        => self::ERR,
            E_CORE_ERROR        => self::ERR,
            E_RECOVERABLE_ERROR => self::ERR,
            E_STRICT            => self::DEBUG,
        );
        // PHP 5.3.0+
        if (defined('E_DEPRECATED')) {
            $this->_errorHandlerMap['E_DEPRECATED'] = self::DEBUG;
        }
        if (defined('E_USER_DEPRECATED')) {
            $this->_errorHandlerMap['E_USER_DEPRECATED'] = self::DEBUG;
        }

        $this->_registeredErrorHandler = true;
        return $this;
    }

    /**
     * Регистрация лога как обработчика исключений
     *
     * @return Core_Log
     */
    public function registerExceptionHandler()
    {
        // Only register once.  Avoids loop issues if it gets registered twice.
        if ($this->_registeredExceptionHandler) {
            return $this;
        }

        $this->_origExceptionHandler = set_exception_handler(array($this, 'exceptionHandler'));

        $this->_registeredExceptionHandler = true;
        return $this;
    }

    /**
     * Error Handler will convert error into log message, and then call the original error handler
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     * @return boolean
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (isset($this->_errorHandlerMap[$errno])) {
            $priority = $this->_errorHandlerMap[$errno];
        } else {
            $priority = self::INFO;
        }
        $this->log($errstr, $priority, $errfile, $errline);

        if ($this->_origErrorHandler !== null) {
            return call_user_func($this->_origErrorHandler, $errno, $errstr, $errfile, $errline, $errcontext);
        }
        return false;
    }

    /**
     * Обработчик исключений
     *
     * @param Exception $e
     * @return bool|mixed
     */
    public function exceptionHandler(Exception $e)
    {
        $priority = self::ERR;
        $this->log($e->getMessage() . PHP_EOL . $e->getTraceAsString(), $priority, $e->getFile(), $e->getLine());

        if ($this->isThrowException()) {
            print($e);
        }

        if ($this->_origExceptionHandler !== null) {
            return call_user_func($this->_origExceptionHandler, $e);
        }
        return false;
    }
    
    /**
     * Получение строки лога в соответствии с установленным форматом
     *
     * @param string $message
     * @param integer $priority
     * @param string $file
     * @param integer $line
     * @return string 
     */
    protected function _formatter($message, $priority, $file, $line)
    {
        if (isset($this->_priorities[$priority])) {
            $priority = $this->_priorities[$priority];
        } else {
            $priority = $this->_priorities[self::INFO];
        }
        $placeholders = array(
            '%date%',
            '%priority%',
            '%message%',
            '%file%',
            '%line%'
        );
        $values = array(
            date($this->getTimeFormat()),
            $priority,
            $message,
            $file,
            $line
        );
        
        return str_replace($placeholders, $values, $this->getFormat());
    }
    
    
}