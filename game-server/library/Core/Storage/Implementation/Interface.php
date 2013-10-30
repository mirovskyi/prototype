<?php

/**
 *
 * @author aleksey
 */
interface Core_Storage_Implementation_Interface 
{

    /**
     * Установка параметров хранилища
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * Получение значения ключа
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * Установка значение ключа
     * @param $key
     * @param $value
     * @return bool
     */
    public function set($key, $value);

    /**
     * Добавление записи
     * @param $key
     * @param $value
     * @return bool
     */
    public function add($key, $value);

    /**
     * Удаление ключа
     * @param $key
     * @return bool
     */
    public function delete($key);

    /**
     * Блокировка ключа
     * @param $key
     * @param int $timeout
     * @return bool
     */
    public function lock($key, $timeout = Core_Storage::DEFAULT_LOCK_TIMEOUT);

    /**
     * Получение ID процесса блокирующего запись
     *
     * @abstract
     * @param $key
     */
    public function getLockPid($key);

    /**
     * Проверка блокировки ключа
     * @param $key
     * @return bool
     */
    public function isLocked($key);

    /**
     * Разблокировка ключа
     * @param $key
     * @return bool
     */
    public function unlock($key);
    
}