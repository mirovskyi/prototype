<?php

 
class Core_Cli_Response
{

    /**
     * Тело ответа
     *
     * @var string
     */
    protected $_body = '';

    /**
     * Флаг вывода исключений
     *
     * @var bool
     */
    protected static $_renderExceptions = false;

    /**
     * Массив объектов исключений
     *
     * @var array
     */
    protected $_exceptions = array();


    /**
     * Установка контента ответа
     *
     * @param string $body
     * @return Core_Cli_Response
     */
    public function setBody($body)
    {
        $this->_body = $body;
        return $this;
    }

    /**
     * Получение контента ответа
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Добавление контента в конец тела ответа
     *
     * @param string $content
     * @return Core_Cli_Response
     */
    public function appendBody($content)
    {
        $this->_body .= $content;
        return $this;
    }

    /**
     * Добавление контента в начало тела ответа
     *
     * @param string $content
     * @return Core_Cli_Response
     */
    public function prependBody($content)
    {
        $this->_body = $content . $this->_body;
        return $this;
    }

    /**
     * Установка флага вывода исключений
     *
     * @param bool $renderExceptions
     * @return Core_Cli_Response
     */
    public static function setRenderExceptions($renderExceptions = true)
    {
        self::$_renderExceptions = $renderExceptions;
    }

    /**
     * Проверка флага вывода исключений
     *
     * @return bool
     */
    public static function renderExceptions()
    {
        return self::$_renderExceptions;
    }

    /**
     * Установка объекта исключения
     *
     * @param Exception $exception
     * @return Core_Cli_Response
     */
    public function setException(Exception $exception)
    {
        $this->_exceptions[] = $exception;
        return $this;
    }

    /**
     * Получение списка исключений
     *
     * @return array
     */
    public function getExceptions()
    {
        return $this->_exceptions;
    }

    /**
     * Проверка наличия исключений
     *
     * @return bool
     */
    public function isException()
    {
        return (bool) count($this->_exceptions);
    }

    /**
     * Вывод тела ответа
     *
     * @return void
     */
    public function render()
    {
        echo $this . PHP_EOL;
    }

    /**
     * Магический метод __toString
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->isException() && $this->renderExceptions()) {
            $exception = '';
            foreach($this->getExceptions() as $e) {
                $exception .= $e->__toString() . PHP_EOL;
            }
            return $exception;
        }
        
        return $this->_body;
    }


}
