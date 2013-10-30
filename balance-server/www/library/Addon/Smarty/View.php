<?php

/**
 * Подключаем библиотеку шаблонизатора Smarty
 */
require_once APPLICATION_PATH . '/../vendor/Smarty/libs/Smarty.class.php';

/**
 * Класс шаблонизатора Smarty для Zend
 *
 * @author aleksey
 */
class Addon_Smarty_View extends Zend_View_Abstract
{
    
    /**
     * Объект Smarty шаблонизатора
     * @var Smarty 
     */
    private $_smarty;
    
    /**
     * __construct
     * @param array $config 
     */
    public function __construct($config = array()) 
    {
        //Установка конфигов
        parent::__construct($config);
        //Создание объект Smarty
        $this->_smarty = new Smarty();
        //Установка конфигов Smarty
        if (isset($config['smarty']['template_dir'])) {
            $this->_smarty->setTemplateDir($config['smarty']['template_dir']);
        }
        if (isset($config['smarty']['compile_dir'])) {
            $this->_smarty->setCompileDir($config['smarty']['compile_dir']);
        }
        if (isset($config['smarty']['config_dir'])) {
            $this->_smarty->setConfigDir($config['smarty']['config_dir']);
        }
        if (isset($config['smarty']['cache_dir'])) {
            $this->_smarty->setCacheDir($config['smarty']['cache_dir']);
        }
    }

    /**
     * Получение обьекта переменых
     *
     * @return array|string
     */
    public function getVars() {
        return $this->_smarty->getTemplateVars();
    }
    
    /**
     * Получение объекта шаблонизатора
     * @return Smarty
     */
    public function getEngine() 
    {
        return $this->_smarty;
    }
    
    /**
     * Магический метод __set
     * @param string $key
     * @param mixed $val
     * @return void 
     */
    public function __set($key, $val) 
    {
        $this->_smarty->assign($key, $val);
        return;
    }
    
    /**
     * Магический метод __isset
     * @param string $key
     * @return boolean 
     */
    public function __isset($key) 
    {
        if ($this->_smarty->getTemplateVars($key) != null) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Магический метод __unset
     * @param string $key 
     */
    public function __unset($key)
    {
        $this->_smarty->clearAssign($key);
    }

    /**
     * Вывод сформированного контента шаблона
     *
     * @return string
     */
    protected function _run()
    {
        $this->_smarty->assign('zend', $this);
        echo $this->_smarty->fetch(func_get_arg(0));
    }
    
}