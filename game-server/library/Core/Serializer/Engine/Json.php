<?php

/**
 * Description of Json
 *
 * @author aleksey
 */
class Core_Serializer_Engine_Json implements Core_Serializer_EngineInterface
{
    
    public function encode(array $options) 
    {
        $json = '';
        if (is_array($options)) {
            $json = json_encode($options, 0);
        }
        
        return $json;
    }
    
    public function decode($serialized) 
    {
        $options = json_decode($serialized, true);
        if ($options === null) {
            throw new Core_Serializer_Exception('Invalid string given to json decode');
        }
        return $options;
    }
    
}