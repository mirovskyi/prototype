<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 13.03.12
 * Time: 15:31
 *
 * Интерфейс доступа к данным сущности
 */
interface App_Model_Mapper_Interface
{

    /**
     * Поиск данных модели по ключу в хранилище
     *
     * @abstract
     * @param mixed $key
     * @param App_Model_Interface $model
     * @return bool
     */
    public function find($key, App_Model_Interface $model);

    /**
     * Сохранение данных модели
     *
     * @abstract
     * @param App_Model_Interface $model
     * @return bool|int
     */
    public function save(App_Model_Interface $model);

    /**
     * Удаление данных модели
     *
     * @abstract
     * @param App_Model_Interface $model
     * @return bool
     */
    public function delete(App_Model_Interface $model);

    /**
     * Блокировка данных модели
     *
     * @abstract
     * @param App_Model_Interface $model
     */
    public function lock(App_Model_Interface $model);

    /**
     * Разблокировка данных модели
     *
     * @abstract
     * @param App_Model_Interface $model
     */
    public function unlock(App_Model_Interface $model);

    /**
     * Проверка блокировки данных модели
     * В случае передачи параметра pid, проверяется блокировка данных модели указанным процессом
     *
     * @abstract
     * @param App_Model_Interface $model
     * @param string|null $pid
     * @return bool
     */
    public function isLock(App_Model_Interface $model, $pid = null);

}
