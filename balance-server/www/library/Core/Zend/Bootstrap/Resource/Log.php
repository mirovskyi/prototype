<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 21.09.12
 * Time: 18:28
 *
 * Ресурс лога
 */
class Core_Zend_Bootstrap_Resource_Log extends Zend_Application_Resource_ResourceAbstract
{

    /**
     * @var Zend_Log
     */
    protected $_log;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Log
     */
    public function init()
    {
        return $this->getLog();
    }

    /**
     * Attach logger
     *
     * @param  Zend_Log $log
     * @return Zend_Application_Resource_Log
     */
    public function setLog(Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }

    public function getLog()
    {
        if (null === $this->_log) {
            $options = $this->getOptions();
            //Формирование пути к файлу лога
            if (isset($options['stream']['writerParams']['stream'])) {
                $path = $options['stream']['writerParams']['stream'];
                //Заменяем плейсхолдер даты на текущую дату
                $path = str_replace('%date%', date('Y-m-d'), $path);
                //Установка пути в настройки лога
                $options['stream']['writerParams']['stream'] = $path;
            }
            //Проверка наличяи флага обработчика ошибок
            $errorHandler = false;
            if (isset($options['errorHandler'])) {
                //Если установлен флаг обработчика ошибок, регистрируем логирование ошибок
                if ($options['errorHandler']) {
                    $errorHandler = true;
                }
                //Удаление флага из конфигов
                unset($options['errorHandler']);
            }
            //Создание объекта лога
            $log = Zend_Log::factory($options);
            //Установка обработчика ошибок
            if ($errorHandler) {
                $log->registerErrorHandler();
            }
            $this->setLog($log);
            //Регистрация лога в реестр
            Zend_Registry::set('log', $log);
        }
        return $this->_log;
    }
}
