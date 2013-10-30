<?php

/**
 * XML generator adapter based on XMLWriter
 */
class Core_Xml_Generator_XmlWriter extends Core_Xml_Generator_GeneratorAbstract
{
    /**
     * XMLWriter instance
     *
     * @var XMLWriter
     */
    protected $_xmlWriter;

    /**
     * Initialized XMLWriter instance
     *
     * @return void
     */
    protected function _init()
    {
        $this->_xmlWriter = new XMLWriter();
        $this->_xmlWriter->openMemory();
        $this->_xmlWriter->startDocument('1.0', $this->_encoding);
    }


    /**
     * Open a new XML element
     *
     * @param string $name XML element name
     * @return void
     */
    protected function _openElement($name)
    {
        $this->_xmlWriter->startElement($name);
    }
    
    /**
     * Add attribute in XML element
     * 
     * @param string $name
     * @param string $value 
     */
    protected function _addAttribute($name, $value) 
    {
        $this->_xmlWriter->writeAttribute($name, $value);
    }

    /**
     * Write XML text data into the currently opened XML element
     *
     * @param string $text XML text data
     * @return void
     */
    protected function _writeTextData($text)
    {
        $this->_xmlWriter->text($text);
    }
    
    /**
     * Write XML content data into the currently opened XML element
     *
     * @param string $xml XML content data
     * @return void
     */
    protected function _writeXmlData($xml)
    {
        $this->_xmlWriter->writeRaw($xml);
    }

    /**
     * Close an previously opened XML element
     *
     * @param string $name
     * @return void
     */
    protected function _closeElement($name)
    {
        $this->_xmlWriter->endElement();

        return $this;
    }

    public function saveXml()
    {
        $xml = $this->_xmlWriter->flush(false);
        return $xml;
    }
}
