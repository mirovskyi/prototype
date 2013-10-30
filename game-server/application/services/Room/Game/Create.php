<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 02.03.12
 * Time: 15:32
 *
 * Фабричный метод получения экземпляра класса реализации алгоритма создания игового стола
 */
class App_Service_Room_Game_Create
{

    /**
     * Получение объекта реализации алгоритма создания игрового стола
     *
     * @static
     * @param string $gameName Наименование игры
     * @param App_Model_Session_User|null $user Объект сессии пользователя (создателя игрового стола)
     * @param array|null $gameParams Параметры создаваемого игрового стола
     * @return App_Service_Room_Game_Templates_Create
     * @throws Core_Exception
     */
    public static function factory($gameName, App_Model_Session_User $user = null, array $gameParams = null)
    {
        //Получаем имя класса реализации
        $className = self::_getImplementationClassName($gameName);

        //Проверка наличия класса
        if (!class_exists($className)) {
            throw new Core_Exception('Creation of the game \'' . $gameName . '\' is not implemented');
        }

        //Инициализация экземпляра класса реализации создания игры
        $object = new $className($user, $gameParams);

        //Проверка реализации объекта абстрации шаблонного метода создания игы
        if (!$object instanceof App_Service_Room_Game_Templates_Create) {
            throw new Core_Exception('Incorrect implementation of the creation game algorithm');
        }

        //Возвращаем экземпляр класса реализации алгоритма создания игрового стола
        return $object;
    }

    /**
     * Получение имени класса реализации создания конкретной игры
     *
     * @static
     * @param string $gameName Наименование игры
     * @return string
     */
    private static function _getImplementationClassName($gameName)
    {
        //Преобразование наименования игры в camelCase формат
        $camelCaseGameName = explode('_', $gameName);
        $camelCaseGameName = array_map('ucfirst', $camelCaseGameName);
        $camelCaseGameName = implode('', $camelCaseGameName);

        //Класс реалиации создания игры
        $className = 'App_Service_Room_Game_Implementations_Create_' . $camelCaseGameName;

        return $className;
    }

}
