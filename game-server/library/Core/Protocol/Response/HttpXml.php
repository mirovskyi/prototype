<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 17.05.12
 * Time: 13:36
 *
 * HTTP ответ сервера в XML формате
 */
class Core_Protocol_Response_HttpXml extends Core_Protocol_Response
{

    /**
     * Получение контента ответа в XML формате
     *
     * @return string
     */
    public function saveXml()
    {
        if (is_string($this->getReturnValue())) {
            //Генератор XML документа
            $generator = Core_Xml::generator($this->getEncoding());
            //Формирование XML документа
            $generator->openElement('response', $this->getReturnValue(), null, true);
            $generator->closeElement('response');

            return $generator->flush();
        } elseif ($this->getReturnValue() instanceof SimpleXMLElement) {
            return $this->getReturnValue()->saveXml();
        }
    }

    /**
     * Переопределение __toString() для отправки HTTP Content-Type заголовка
     *
     * @return string
     */
    public function __toString()
    {
        if (!headers_sent()) {
            header('Content-Type: text/xml; charset=' . strtolower($this->getEncoding()));
        }

        return $this->saveXml();
    }

}
