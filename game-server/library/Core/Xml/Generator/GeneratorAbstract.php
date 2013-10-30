<?php

/**
 * Abstract XML generator adapter
 */
abstract class Core_Xml_Generator_GeneratorAbstract
{
    /**
     * XML encoding string
     *
     * @var string
     */
    protected $_encoding;

    /**
     * Construct new instance of the generator
     *
     * @param string $encoding XML encoding, default UTF-8
     */
    public function __construct($encoding = 'UTF-8')
    {
        $this->_encoding = $encoding;
        $this->_init();
    }

    /**
     * Start XML element
     *
     * Method opens a new XML element with an element name and an optional value
     *
     * @param string $name XML tag name
     * @param string $value Optional value of the XML tag
     * @param null $attributes
     * @param boolean $isXmlContent Optional set value as xml content
     *
     * @internal param array $attributes Optional value of the element attributes
     * @return Core_Xml_Generator_GeneratorAbstract Fluent interface
     */
    public function openElement($name, $value = null, $attributes = null, $isXmlContent = false)
    {
        $this->_openElement($name);
        if ($attributes !== null && is_array($attributes)) {
            foreach($attributes as $k => $v) {
                $this->_addAttribute($k, $v);
            }
        }
        if ($value !== null) {
            if ($isXmlContent) {
                $this->_writeXmlData($value);
            } else {
                $this->_writeTextData($value);
            }
        }

        return $this;
    }

    /**
     * End of an XML element
     *
     * Method marks the end of an XML element
     *
     * @param string $name XML tag name
     * @return Core_Xml_Generator_GeneratorAbstract Fluent interface
     */
    public function closeElement($name)
    {
        $this->_closeElement($name);

        return $this;
    }

    /**
     * Return XML as a string
     *
     * @return string
     */
    abstract public function saveXml();

    /**
     * Return encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Returns the XML as a string and flushes all internal buffers
     *
     * @return string
     */
    public function flush()
    {
        $xml = $this->saveXml();
        $this->_init();
        return $xml;
    }

    /**
     * Returns XML without document declaration
     *
     * @return string
     */
    public function __toString()
    {
        return $this->stripDeclaration($this->saveXml());
    }

    /**
     * Removes XML declaration from a string
     *
     * @param string $xml
     * @return string
     */
    public function stripDeclaration($xml)
    {
        return preg_replace('/<\?xml version="1.0"( encoding="[^\"]*")?\?>\n/u', '', $xml);
    }

    /**
     * Start XML element
     *
     * @param string $name XML element name
     */
    abstract protected function _openElement($name);
    
    /**
     * Add attribute in XML element
     * 
     * 
     * @param string $name attribute name
     * @param string $value attribute value
     */
    abstract protected function _addAttribute($name, $value);

    /**
     * Write XML text data into the currently opened XML element
     *
     * @param string $text
     */
    abstract protected function _writeTextData($text);
    
    /**
     * Write XML content data into the currently opened XML element
     *
     * @param string $xml
     */
    abstract protected function _writeXmlData($xml);

    /**
     * End XML element
     *
     * @param string $name
     */
    abstract protected function _closeElement($name);
}
