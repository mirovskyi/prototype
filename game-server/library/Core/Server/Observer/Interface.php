<?php

/**
 * Observer interface
 */
interface Core_Server_Observer_Interface
{

    /**
     * Обработка события слушателя
     *
     * @abstract
     * @param $observableSubject
     */
    public static function observe($observableSubject);

}