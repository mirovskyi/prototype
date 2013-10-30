<?php


class Core_Protocol_Response
{

    /**
     * Кодировка
     *
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Ответ сервера
     *
     * @var string
     */
    protected $_return;


    /**
     * __construct
     *
     * @param string|null $return
     */
    public function __construct($return = null)
    {
        $this->setReturnValue($return);
    }

    /**
     * Установка кодировки
     *
     * @param string $encoding
     * @return Core_Protocol_Response
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Получение кодировки
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Установка ответа сервера
     *
     * @param string $return
     * @return Core_Protocol_Response
     */
    public function setReturnValue($return)
    {
        $this->_return = $return;
        return $this;
    }

    /**
     * Получение ответа сервера
     *
     * @return string
     */
    public function getReturnValue()
    {
        return $this->_return;
    }

    /**
     * Получение объекта в виде строки
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getReturnValue();
    }

}
 
