<?php

/**
 * Description of Abstract
 *
 * @author aleksey
 */
abstract class Core_Serializer_Abstract implements Core_Serializer_Interface 
{
    
    /**
     * Получение полей класса в виде массива
     *
     * @return array 
     */
    public function __encode() 
    {
        $properties = array();
        $options = array();
        
        $object = clone($this);
        
        if (method_exists($object, '__sleep')) {
            $properties = $object->__sleep();
        } else {
            $r = new ReflectionClass($object);
            foreach($r->getProperties() as $property) {
                $properties[] = $property->getName(); 
            }
        }

        foreach($properties as $property) {
            $value = $object->$property;
            if ($value instanceof Core_Serializer_Abstract) {
                $value = $value->__encode();
            }
            $options[$property] = $value;
        }

        return $options;
    }

    /**
     * Установка значений указанного ассоцитивного массива
     * в качестве значений полей класса
     *
     * @param array $options Ассоциативный массив значений
     */
    public function __decode(array $options) 
    {
        foreach($options as $property => $value) {
            $this->$property = $value;
        }
        
        if (method_exists($this, '__wakeup')) {
            $this->__wakeup();
        }
    }
    
    /**
     * Сериализация объекта
     *
     * @param string $type
     * @return string 
     */
    public function serialize($type = null)
    {
        $options = $this->__encode();
        return Core_Serializer::serialize($options, $type);
    }

    /**
     * Преобразование сериализованной строки в экземпляр класса
     *
     * @param string|array $serialized
     * @param string $type
     * @return Core_Serializer_Abstract 
     */
    public function unserialize($serialized, $type = null) 
    {
        if (is_string($serialized)) {
            $options = Core_Serializer::unserialize($serialized, $type);
        } elseif (is_array($serialized)) {
            $options = $serialized;
        } else {
            throw new Core_Serializer_Exception('Unknown serialized value given to unserialize object');
        }
        
        $this->__decode($options);
        return $this;
    }
    
}