<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.05.12
 * Time: 9:57
 *
 * Интерфейс работы с базой данных истории игр
 */
interface Core_Game_History_Db_Interface
{

    /**
     * Установка настроек
     *
     * @abstract
     * @param array $options
     * @return void
     */
    public function setOptions(array $options);

    /**
     * Флаг записи истории в промежуточное хранилище (кэш)
     *
     * @abstract
     * @return boolean
     */
    public function isCached();

    /**
     * Добавление записи истории
     *
     * @abstract
     * @param Core_Game_History_Db_Record $record Запись истории
     * @param string|null $tableName Имя таблицы
     * @return bool
     */
    public function set(Core_Game_History_Db_Record $record, $tableName = null);

    /**
     * Получение записи из истории
     *
     * @abstract
     * @param Core_Game_History_Db_Record $record Данные записи в истории по которым необходимо вести поиск
     * @param string|null $tableName Имя таблицы
     * @return Core_Game_History_Db_Record|bool
     */
    public function get(Core_Game_History_Db_Record $record, $tableName = null);

    /**
     * Запись данных истории в кэш
     *
     * @abstract
     * @param string $idGame Идентификатор игры
     * @param Core_Game_History_Db_Record $record Запись в истории
     * @return bool
     */
    public function writeCache($idGame, Core_Game_History_Db_Record $record);

    /**
     * Чтение данные истории из кэша
     *
     * @abstract
     * @param string $idGame Идентификатор игры
     * @return Core_Game_History_Db_Record|bool
     */
    public function readCache($idGame);

    /**
     * Удаление записи из кэша
     *
     * @abstract
     * @param string $idGame Идентификатор игры
     * @return bool
     */
    public function deleteCache($idGame);

    /**
     * Запись данных истории из базы данных
     *
     * @abstract
     * @param string $tableName Имя таблицы
     * @param Core_Game_History_Db_Record $record Данные записи в истории
     * @return bool
     */
    public function writeDb($tableName, Core_Game_History_Db_Record $record);

    /**
     * Чтение данных истории из базы данных
     *
     * @abstract
     * @param string $tableName Имя таблицы
     * @param Core_Game_History_Db_Record $record Данные записи по которым необходимо вести поиск
     * @return Core_Game_History_Db_Record|bool
     */
    public function readDb($tableName, Core_Game_History_Db_Record $record);

    /**
     * Удаление данных истории из базы
     *
     * @abstract
     * @param string $tableName Имя таблицы
     * @param Core_Game_History_Db_Record $record Данные записи, которую необходимо удалить
     * @return bool
     */
    public function deleteDb($tableName, Core_Game_History_Db_Record $record);

    /**
     * Фиксация данных истории из кэша в базу данных
     *
     * @abstract

     * @param string $idGame Идентификтор игры
     * @param string $idUser Идентификатор пользователя соц. сети, для которого необходимо сохранить историю
     * @param string $network Имя соц. сети
     *
     * @return bool
     */
    public function commitCache($idGame, $idUser, $network);

    /**
     * Получение списка игр
     *
     * @abstract
     * @param string $tableName Имя таблицы
     * @param string $idUser Идентификатор пользователя в соц. сети
     * @param string $network Имя соц. сети
     * @return array КЛЮЧ МАССИВА - НАЗВАНИЕ ИГРЫ, ЗНАЧЕНИЕ - КОЛИЧЕСТВО СЫГРАННЫХ ПАРТИЙ
     */
    public function getGames($tableName, $idUser, $network);

    /**
     * Получение списка записей в истории игры
     *
     * @abstract
     * @param string $tableName Имя таблицы
     * @param string $idUser Идентификатор пользователя в соц. сети
     * @param string $network Имя соц. сети
     * @param string $game Наименование игры
     * @return Core_Game_History_Db_Record[]
     */
    public function getGameRecords($tableName, $idUser, $network, $game);

    /**
     * Получение количество записей в разделе избранны игр
     *
     * @abstract
     * @return int
     */
    public function getFavoriteRecordsCount();

    /**
     * Получение имени таблицы с записями истории за текущий день
     *
     * @abstract
     * @return string
     */
    public function getCurrentTableName();

    /**
     * Получение имени таблицы с записями истории за предыдущий день
     *
     * @abstract
     * @return string
     */
    public function getPreviousTableName();

    /**
     * Получение имени таблицы с избранными записями в истории
     *
     * @abstract
     * @return string
     */
    public function getFavoriteTableName();

    /**
     * Получение имени тблицы истории за указанную дату
     *
     * @abstract
     * @param string $date Дата в формате 'Y-m-d'
     * @return string
     */
    public function getTableNameByDate($date);

}
