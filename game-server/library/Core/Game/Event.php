<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 07.03.12
 * Time: 11:55
 *
 * Интерфейс события за игровым столом
 */
interface Core_Game_Event
{

    /**
     * Получение типа события
     *
     * @abstract
     * @return string
     */
    public function getType();

    /**
     * Получение уникального имени события
     *
     * @abstract
     * @return string
     */
    public function getName();

    /**
     * Установка объекта игры, к которой применяется событие
     *
     * @abstract
     * @param Core_Game_Abstract $game
     */
    public function setGameObject(Core_Game_Abstract $game);

    /**
     * Получение объекта игры, к которой применяется событие
     *
     * @abstract
     * @return Core_Game_Abstract
     */
    public function getGameObject();

    /**
     * Оповещение пользователя о событии
     *
     * @param string $player
     * @return App_Service_Events_Abstract
     */
    public function notifyPlayer($player);

    /**
     * Удаление игрока из списка оповещенных
     *
     * @param string $playerSid Идентификатор сессии пользователя
     */
    public function clearPlayerNotification($playerSid);

    /**
     * Проверка оповещения пользователя о событии
     *
     * @param string $player
     * @return bool
     */
    public function isPlayerNotified($player);

    /**
     * Обработка события
     *
     * @abstract
     * @return void
     */
    public function handle();

    /**
     * Проверка возможности создания множества подобных событий
     *
     * @abstract
     * @return bool
     */
    public function isSingle();

    /**
     * Проверка завершения работы события
     *
     * @abstract
     * @return bool
     */
    public function isWorkedOut();

    /**
     * Завершение события
     *
     * @abstract
     * @return void
     */
    public function destroy();

    /**
     * Получение данных события в виде строки
     *
     * @abstract
     * @return string
     */
    public function __toString();

}
