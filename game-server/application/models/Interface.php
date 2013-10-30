<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 13.03.12
 * Time: 15:15
 *
 * Интерфейс модели сущности
 */
interface App_Model_Interface
{

    /**
     * Получение ключа записи в хранилище
     *
     * @return string
     */
    public function getKey();

    /**
     * Поиск данных модели
     *
     * @abstract
     * @param string $key
     * @return bool
     */
    public function find($key);

    /**
     * Сохранение данных модели
     *
     * @abstract
     * @return bool
     */
    public function save();

    /**
     * Удаление данных модели
     *
     * @abstract
     * @return bool
     */
    public function delete();

    /**
     * Блокировка данных модели
     *
     * @abstract
     */
    public function lock();

    /**
     * Разблокировка данных модели
     *
     * @abstract
     */
    public function unlock();

    /**
     * Проверка блокировки данных модели
     * В случае передачи параметра pid, проверяется блокировка данных модели указанным процессом
     *
     * @abstract
     * @param string|null $pid
     * @return bool
     */
    public function isLock($pid = null);

    /**
     * Megic method __set
     *
     * @abstract
     * @param $name
     * @param $value
     */
    public function __set($name, $value);

    /**
     * Megic method __get
     *
     * @abstract
     * @param $name
     */
    public function __get($name);

    /**
     * Получение модели в виде строки
     *
     * @abstract
     * @return string
     */
    public function __toString();

}
