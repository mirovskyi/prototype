<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.05.12
 * Time: 16:55
 *
 * Фабрика создания объекта работы с базой данных истории игр
 */
class Core_Game_History_Db
{

    /**
     * Тип базы данных по умолчанию
     */
    const DEFAULT_DB = 'relational';

    /**
     * Метод получения объекта реализации работы с базой данных истории игр
     *
     * @static
     * @param string|null $dbType
     * @param array $options
     * @return Core_Game_History_Db_Interface
     * @throws Core_Game_Exception
     */
    public static function factory($dbType = null, $options = array())
    {
        //Тип базы данных
        if (null === $dbType) {
            $dbType = self::DEFAULT_DB;
        }
        //Формирование имени класса реализации
        $impl = ucfirst($dbType);
        $className = __CLASS__ . '_' . $impl;
        //Инициализация класса базы данных
        if (!class_exists($className)) {
            throw new Core_Game_Exception('Unknown history database implementation \'' . $impl . '\'');
        }
        return new $className($options);
    }

}
