<?php

/**
 *
 * @author aleksey
 */
interface Core_Serializer_Interface 
{
    
    /**
     * Сериализация данных объекта
     * 
     * @param string $type [optional] Тип сериализации
     * @return array $options Массив значений объекта
     */
    public function serialize($type = null);
    
    /**
     * Установка значений своиств класса
     * 
     * @param array $options Массив значений своиств класса
     * @param string $type [optional]
     * @return mixed
     */
    public function unserialize($options, $type = null);
    
}