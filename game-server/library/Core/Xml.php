<?php

 
class Core_Xml
{

    /**
     * Генератор XML документа
     *
     * @static
     * @param string $encoding
     * @return Core_Xml_Generator_DomDocument|Core_Xml_Generator_XmlWriter
     */
    public static function generator($encoding = 'UTF-8')
    {
        if (extension_loaded('xmlwriter')) {
            return new Core_Xml_Generator_XmlWriter($encoding);
        } else {
            return new Core_Xml_Generator_DomDocument($encoding);
        }
    }

}
