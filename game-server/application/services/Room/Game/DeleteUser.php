<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 26.03.12
 * Time: 10:35
 *
 * Фабричный метод получения экземпляра класса реализации алгоритма удаления игрока из игры
 */
class App_Service_Room_Game_DeleteUser
{

    /**
     * Получение объекта реализации алгоритма удаления игрока из игры
     *
     * @static
     * @param string $gameName Наименование игры
     * @param App_Model_Session_Game|null $game Объект сессии игрового стола в игровом зале
     * @param App_Model_Session_User|null $user Объект сессии пользователя в игровом зале
     * @return App_Service_Room_Game_Templates_DeleteUser
     * @throws Core_Exception
     */
    public static function factory($gameName, App_Model_Session_Game $game = null, App_Model_Session_User $user = null)
    {
        //Получаем имя класса реализации
        $className = self::_getImplementationClassName($gameName);

        //Проверка наличия класса
        if (!class_exists($className)) {
            throw new Core_Exception('Delete user from the game \'' . $gameName . '\' is not implemented');
        }

        //Инициализация экземпляра класса реализации удаления игрока
        $object = new $className($game, $user);

        //Проверка реализации объекта абстрации шаблонного метода удаления игрока из игровой стол
        if (!$object instanceof App_Service_Room_Game_Templates_DeleteUser) {
            throw new Core_Exception('Incorrect implementation of the delete user from the game algorithm');
        }

        //Возвращаем экземпляр класса реализации алгоритма добавления игрока за игровой стол
        return $object;
    }

    /**
     * Получение имени класса реализации удаления игрока из игровой стол
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
        $className = 'App_Service_Room_Game_Implementations_DeleteUser_' . $camelCaseGameName;

        return $className;
    }

}
