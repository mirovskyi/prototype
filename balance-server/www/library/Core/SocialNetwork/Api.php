<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 02.10.12
 * Time: 18:18
 *
 * Фабричный метод получения реализации API соц. сети
 */
class Core_SocialNetwork_Api
{

    /**
     * Получение объекта реализации API соц. сети
     *
     * @param string $nameService Наименование соц. сети
     *
     * @return Core_SocialNetwork_ApiInterface
     * @throws Core_Exception
     */
    public static function factory($nameService)
    {
        //Формирование имени класса реализации API соц. сети
        $className = 'Core_SocialNetwork_' . ucfirst($nameService) . '_Api';
        //Проверка наличия класса
        if (!class_exists($className)) {
            throw new Core_Exception($nameService . ' API implementation does not exists');
        }
        //Создание объекта
        return new $className();
    }

}
