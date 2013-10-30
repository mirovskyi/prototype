<?php

 
class Core_Protocol_Server_Fault extends Core_Protocol_Fault
{

    /**
     * @var Exception
     */
    protected $_exception;

    /**
     * @var array Array of exception classes that may define faults
     */
    protected static $_faultExceptionClasses = array('Exception' => true);

    /**
     * @var array Array of fault observers
     */
    protected static $_observers = array();

    /**
     * Constructor
     *
     * @param Exception $e
     * @return Core_Protocol_Server_Fault
     */
    public function __construct(Exception $e)
    {
        $this->_exception = $e;
        $code             = 500;
        $message          = 'Internal Server Error';
        $type             = Core_Exception::SYSTEM;
        $exceptionClass   = get_class($e);

        foreach (array_keys(self::$_faultExceptionClasses) as $class) {
            if ($e instanceof $class) {
                $code    = $e->getCode();
                $message = $e->getMessage();
                if ($e instanceof Core_Exception) {
                    $type = $e->getType();
                }
                break;
            }
        }

        parent::__construct($code, $message, $type);

        // Notify exception observers, if present
        if (!empty(self::$_observers)) {
            foreach (array_keys(self::$_observers) as $observer) {
                call_user_func(array($observer, 'observe'), $this);
            }
        }
    }

    /**
     * Return Core_Protocol_Server_Fault instance
     *
     * @param Exception $e
     * @return Core_Protocol_Server_Fault
     */
    public static function getInstance(Exception $e)
    {
        return new self($e);
    }

    /**
     * Attach valid exceptions that can be used to define faults
     *
     * @param string|array $classes Class name or array of class names
     * @return void
     */
    public static function attachFaultException($classes)
    {
        if (!is_array($classes)) {
            $classes = (array) $classes;
        }

        foreach ($classes as $class) {
            if (is_string($class) && class_exists($class)) {
                self::$_faultExceptionClasses[$class] = true;
            }
        }
    }

    /**
     * Detach fault exception classes
     *
     * @param string|array $classes Class name or array of class names
     * @return void
     */
    public static function detachFaultException($classes)
    {
        if (!is_array($classes)) {
            $classes = (array) $classes;
        }

        foreach ($classes as $class) {
            if (is_string($class) && isset(self::$_faultExceptionClasses[$class])) {
                unset(self::$_faultExceptionClasses[$class]);
            }
        }
    }

    /**
     * Attach an observer class
     *
     * Allows observation of server faults, thus allowing logging or mail
     * notification of fault responses on the server.
     *
     * Expects a valid class name; that class must have a public static method
     * 'observe' that accepts an exception as its sole argument.
     *
     * @param string $class
     * @return boolean
     */
    public static function attachObserver($class)
    {
        if (!is_string($class)
            || !class_exists($class)
            || !is_callable(array($class, 'observe')))
        {
            return false;
        }

        if (!isset(self::$_observers[$class])) {
            self::$_observers[$class] = true;
        }

        return true;
    }

    /**
     * Detach an observer
     *
     * @param string $class
     * @return boolean
     */
    public static function detachObserver($class)
    {
        if (!isset(self::$_observers[$class])) {
            return false;
        }

        unset(self::$_observers[$class]);
        return true;
    }

    /**
     * Retrieve the exception
     *
     * @access public
     * @return Exception
     */
    public function getException()
    {
        return $this->_exception;
    }

}
