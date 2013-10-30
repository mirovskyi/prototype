<?php

/**
 * Подключение интерфейса обработчика
 */
require_once 'listener/HandlerInterface.php';

/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 04.10.12
 * Time: 9:09
 *
 * Класс слушателя
 */
class Listener
{

    /**
     * Параметры действия
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Обработчики событий
     *
     * @var array
     */
    protected $_handlers = array(
        'gift',
        'invite'
    );


    /**
     * Создание объекта слушателя
     *
     * @param array  $params Параметры действия
     */
    public function __construct(array $params = null)
    {
        if (null !== $params) {
            $this->setParams($params);
        }
    }

    /**
     * Установка параметров действия
     *
     * @param $params
     */
    public function setParams($params)
    {
        $this->_params = $params;
    }

    /**
     * Получение параметров действия
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Установка наименований обработчиков
     *
     * @param array $handlers
     */
    public function setHandlers(array $handlers)
    {
        $this->_handlers = $handlers;
    }

    /**
     * Получение наименований обработчиков
     *
     * @return array
     */
    public function getHandlers()
    {
        return $this->_handlers;
    }

    /**
     * Добавление имени обработчика
     *
     * @param string $handler
     */
    public function addHandler($handler)
    {
        if (!in_array($handler, $this->_handlers)) {
            $this->_handlers[] = $handler;
        }
    }

    /**
     * Удаление имени обработчика
     *
     * @param string $handler
     */
    public function delHandler($handler)
    {
        $index = array_search($handler, $this->_handlers);
        if (false !== $index) {
            unset($this->_handlers[$index]);
        }
    }

    /**
     * Получение объекта обработчика
     *
     * @param $handler
     * @return HandlerInterface
     * @throws Exception
     */
    public function getHandlerObject($handler)
    {
        //Подключение файла обработчика
        $filename = realpath(dirname(__FILE__) . '/listener') . DIRECTORY_SEPARATOR . $handler . '.php';
        if (file_exists($filename)) {
            require_once $filename;
        } else {
            throw new Exception('Handler file ' . $filename . '.php does not exists');
        }
        //Создание объекта слушателя
        $className = ucfirst($this->_camelCase($handler));
        if (!class_exists($className)) {
            throw new Exception('Class ' . $className . ' was not found');
        }

        return new $className();
    }

    /**
     * Обработка действия (события)
     *
     * @return void
     */
    public function handle()
    {
        //Проход по всем зарегистрированным обработчикам
        foreach($this->getHandlers() as $handlerName) {
            //Получение объекта обработчика
            $handler = $this->getHandlerObject($handlerName);
            //Проверка наличия события
            if ($handler->hasEvent($this->getParams())) {
                //Обработка события
                $handler->handle($this->getParams());
            }
        }
    }

    /**
     * Преобразование строки в camelCase
     *
     * @param string $str
     * @return string
     */
    private function _camelCase($str)
    {
        $parts = explode('_', $str);
        for($i = 1; $i < count($parts); $i++) {
            $parts[$i] = ucfirst($parts[$i]);
        }

        return implode('', $parts);
    }

}
