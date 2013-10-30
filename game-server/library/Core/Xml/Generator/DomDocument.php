<?php

/**
 * DOMDocument based implementation of a XML generator
 */
class Core_Xml_Generator_DomDocument extends Core_Xml_Generator_GeneratorAbstract
{
    /**
     * @var DOMDocument
     */
    protected $_dom;

    /**
     * @var DOMNode
     */
    protected $_currentElement;

    /**
     * Start XML element
     *
     * @param string $name
     * @return void
     */
    protected function _openElement($name)
    {
        $newElement = $this->_dom->createElement($name);

        $this->_currentElement = $this->_currentElement->appendChild($newElement);
    }
    
    /**
     * Add attribute in XML element
     * 
     * @param string $name
     * @param string $value 
     */
    protected function _addAttribute($name, $value) 
    {
        $newAttr = $this->_dom->createAttribute($name);
        
        $newAttr->value = $value;
        $this->_currentElement->appendChild($newAttr);
    }

    /**
     * Write XML text data into the currently opened XML element
     *
     * @param string $text
     */
    protected function _writeTextData($text)
    {
        $this->_currentElement->appendChild($this->_dom->createTextNode($text));
    }
    
    /**
     * Write XML content data into the currently opened XML element
     *
     * @param string $xml
     */
    protected function _writeXmlData($xml)
    {
        $simpleXMLElement = new SimpleXMLElement($xml);
        $domSimpleElement = dom_import_simplexml($simpleXMLElement);
        $element = $this->_dom->importNode($domSimpleElement, true);
        $this->_currentElement->appendChild($element);
    }

    /**
     * Close an previously opened XML element
     *
     * Resets $_currentElement to the next parent node in the hierarchy
     *
     * @param string $name
     * @return void
     */
    protected function _closeElement($name)
    {
        if (isset($this->_currentElement->parentNode)) {
            $this->_currentElement = $this->_currentElement->parentNode;
        }
    }

    /**
     * Save XML as a string
     *
     * @return string
     */
    public function saveXml()
    {
        return $this->_dom->saveXml();
    }

    /**
     * Initializes internal objects
     *
     * @return void
     */
    protected function _init()
    {
        $this->_dom = new DOMDocument('1.0', $this->_encoding);
        $this->_currentElement = $this->_dom;
    }
}