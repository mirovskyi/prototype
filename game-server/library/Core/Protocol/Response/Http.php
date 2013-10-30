<?php

 /**
 * HTTP ответ сервера
 */
class Core_Protocol_Response_Http extends Core_Protocol_Response
{

    /**
     * Переопределение __toString() для отправки HTTP Content-Type заголовка
     *
     * @return string
     */
    public function __toString()
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=' . strtolower($this->getEncoding()));
        }

        return parent::__toString();
    }

}
