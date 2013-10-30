<?php

/**
 * Description of Interface
 *
 * @author aleksey
 */
interface Core_Storage_Cascade_Interface 
{
    
    /**
     * Чтение данных из кэша
     */
    public function readCache();
    
    /**
     * Чтение данных из БД
     */
    public function readDb();
    
    /**
     * Запись данных в кэш
     */
    public function writeCache();
    
    /**
     * Запись данных в БД
     */
    public function writeDb();
    
}