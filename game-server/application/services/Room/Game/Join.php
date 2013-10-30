<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 02.03.12
 * Time: 15:32
 *
 * Фабричный метод получения экземпляра класса реализации алгоритма добавления игрока за игровой стол
 */
class App_Service_Room_Game_Join
{

    /**
     * Получение объекта реализации алгоритма добавления игрока за игровой стол
     *
     * @static
     * @param string $gameName Наименование игры
     * @param App_Model_Session_Game|null $game Объект сессии игрового стола в игровом зале
     * @param App_Model_Session_User|null $user Объект сессии пользователя в игровом зале
     * @param bool $watcher Флаг добавления пользователя в качестве наблюдателя
     * @return App_Service_Room_Game_Templates_Join
     * @throws Core_Exception
     */
    public static function factory($gameName, App_Model_Session_Game $game = null, App_Model_Session_User $user = null, $watcher = false)
    {
        //Получаем имя класса реализации
        $className = self::_getImplementationClassName($gameName);

        //Проверка наличия класса
        if (!class_exists($className)) {
            throw new Core_Exception('Joining user in the game \'' . $gameName . '\' is not implemented');
        }

        //Инициализация экземпляра класса реализации создания игры
        $object = new $className($game, $user, $watcher);

        //Проверка реализации объекта абстрации шаблонного метода добавления игрока за игровой стол
        if (!$object instanceof App_Service_Room_Game_Templates_Join) {
            throw new Core_Exception('Incorrect implementation of the joining user in the game algorithm');
        }

        //Возвращаем экземпляр класса реализации алгоритма добавления игрока за игровой стол
        return $object;
    }

    /**
     * Получение имени класса реализации добавления игрока за игровой стол
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
        $className = 'App_Service_Room_Game_Implementations_Join_' . $camelCaseGameName;

        return $className;
    }

}
