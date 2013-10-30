<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 09.05.12
 * Time: 11:52
 *
 * Фабричный метод получения экземпляра класса реализации алгоритма рестарта игрового стола
 */
class App_Service_Room_Game_Restart
{

    /**
     * Получение объекта реализации алгоритма рестарта игрового стола
     *
     * @static
     * @param string $gameName Наименование игры
     * @param App_Model_Session_Game|null $game Объект сессии игрового стола в игровом зале
     * @param App_Model_Session_User|null $user Объект сессии пользователя в игровом зале
     * @return App_Service_Room_Game_Templates_Restart
     * @throws Core_Exception
     */
    public static function factory($gameName, App_Model_Session_Game $game = null, App_Model_Session_User $user = null)
    {
        //Получаем имя класса реализации
        $className = self::_getImplementationClassName($gameName);

        //Проверка наличия класса
        if (!class_exists($className)) {
            throw new Core_Exception('Restart game \'' . $gameName . '\' is not implemented');
        }

        //Инициализация экземпляра класса реализации рестарта игры
        $object = new $className($game, $user);

        //Проверка реализации объекта абстрации шаблонного метода рестарта игрового стола
        if (!$object instanceof App_Service_Room_Game_Templates_Restart) {
            throw new Core_Exception('Incorrect implementation of the restart game algorithm');
        }

        //Возвращаем экземпляр класса реализации алгоритма рестарта игрового стола
        return $object;
    }

    /**
     * Получение имени класса реализации рестарта игрового стола
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
        $className = 'App_Service_Room_Game_Implementations_Restart_' . $camelCaseGameName;

        return $className;
    }

}
