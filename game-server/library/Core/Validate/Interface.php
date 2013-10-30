<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 17.08.12
 * Time: 9:29
 *
 * Интерфейс валидатора
 */
interface Core_Validate_Interface
{

    /**
     * Установка параметров валидатора
     *
     * @abstract
     *
     * @param array $options
     *
     * @return void
     */
    public function setOptions(array $options = array());

    /**
     * Проверка валидности значения
     *
     * @abstract
     *
     * @param mixed    $value Значение для валидации
     *
     * @return bool
     */
    public function valid($value);

    /**
     * Получение кода ошибки валидации
     *
     * @abstract
     * @return int
     */
    public function getErrorCode();

    /**
     * Получение сообщения об ошибке валидации
     *
     * @abstract
     * @return string
     */
    public function getErrorMessage();

}
