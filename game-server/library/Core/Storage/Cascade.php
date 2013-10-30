<?php

/**
 * Description of Cascade
 *
 * @author aleksey
 */
class Core_Storage_Cascade 
{
    
    /**
     * Каскадная запись данных модели в хранилище
     *
     * @param Core_Storage_Cascade_Interface $model
     * @return bool 
     */
    public static function write(Core_Storage_Cascade_Interface $model)
    {
        //Запись данных в базу данных
        if (!$model->writeDb()) {
            return false;
        }
        
        //Запись данных в кэш
        $model->writeCache();
        return true;
    }
    
    /**
     * Каскадное чтения данных модели из хранилища
     *
     * @param Core_Storage_Cascade_Interface $model
     * @return bool 
     */
    public static function read(Core_Storage_Cascade_Interface $model) 
    {
        //Попытка чтения из кэша
        if ($model->readCache()) {
            return true;
        }
        
        //Попытка чтения из базы данных
        if ($model->readDb()) {
            //Попытка записи полученных данных в кэш
            $model->writeCache();
            return true;
        }
        
        return false;
    }
    
}