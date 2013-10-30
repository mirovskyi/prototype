<?php

/**
 * Description of Loader
 *
 * @author aleksey
 */
class Console_Core_Loader 
{
    
    public static function registerAutoload()
    {
        spl_autoload_register(array(__CLASS__, 'includeClass'));
    }
    
    public static function unregisterAutoload()
    {
        spl_autoload_unregister(array(__CLASS__, 'inclideClass'));
    }
    
    public static function includeClass($class)
    {
        if (preg_match('/[^_]+Command$/', $class)) {
            $command = substr($class, strrpos($class, '_'));
            $class = str_replace($command, '_commands' . $command, $class);
        } elseif (strstr($class, '_Model_')) {
            $class = str_replace('Model', 'models', $class);
        }
        $path = str_replace('_', DIRECTORY_SEPARATOR, $class);
        require_once $path . '.php';
    }
    
}