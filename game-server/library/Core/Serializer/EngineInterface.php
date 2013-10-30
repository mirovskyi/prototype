<?php

/**
 * Description of EngineAbstract
 *
 * @author aleksey
 */
interface Core_Serializer_EngineInterface 
{
    
    /**
     * Преобразование массива данных в строку
     * 
     * @param array $options Массив данных
     * @return string
     */
    public function encode(array $options);
    
    /**
     * Преобразование строки в массив данных
     * 
     * @param string serialized Сериализованная строка данных
     * @return array
     */
    public function decode($serialized);
    
}