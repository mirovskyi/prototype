<?php

/**
 * Description of Bootstrap
 *
 * @author aleksey
 */
class Console_Bootstrap 
{
    
    /**
     * Конфиги 
     * @var Zend_Config 
     */
    protected $_options;
    
    /**
     * __construct
     * @param array|string $options 
     */
    public function __construct($options = null) 
    {
        if ($options) {
            if (is_array($options)) {
                $this->_options = new Zend_Config($options);
            } elseif (is_string($options)) {
                if (preg_match('/\.(?P<extention>\D+)$/', $options, $match)) {
                    $extention = $match['extention'];
                    switch ($extention) {
                        case 'ini': 
                            $this->_options = new Zend_Config_Ini($options, 'console');
                            break;
                        case 'xml':
                            $this->_options = new Zend_Config_Xml($options, 'console');
                            break;
                        case 'php': 
                            $this->_options = new Zend_Config(require $options);
                            break;
                        case 'yml': 
                            $this->_options = new Zend_Config_Yaml($options, 'console');
                            break;
                    }
                }
            }
        }
    }
    
    /**
     * Метод получения настроек
     * @return Zend_Config 
     */
    public function getOptions()
    {
        if ($this->_options instanceof Zend_Config) {
            return $this->_options->get(CONSOLE_ENV, $this->_options);
        }
        return $this->_options;
    }
    
    /**
     * Метод получения параметра настроек
     * @param string $name
     * @return mixed 
     */
    public function getOption($name)
    {
        return $this->getOptions()->$name;
    }
    
    /**
     * Start bootstraping
     */
    public function start()
    {
        $r = new Zend_Reflection_Class($this);
        foreach($r->getMethods(Zend_Reflection_Method::IS_PROTECTED) as $method) {
            $methodName = $method->getName();
            if (substr($methodName, 0, 5) == '_init') {
                $this->$methodName();
            }
        }
        $front = Console_Core_Front::getInstance();
        $front->bootstrap($this)
              ->dispatch();
    }
    
    protected function _initDatabase()
    {
        $dbConfig = $this->getOption('database');
        //Создания адаптера БД
        $dbAdapter = Zend_Db::factory($dbConfig);
        //Установка адаптера по умолчанию
        Zend_Db_Table::setDefaultAdapter($dbAdapter);
        //Сохранение адаптера в регистре
        Zend_Registry::set('db', $dbAdapter);
    }
    
}