<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 17.08.12
 * Time: 9:35
 *
 * Валидатор целого (integer) числа
 */
class Core_Validate_Int implements Core_Validate_Interface
{

    /**
     * Код ошибки валидации
     */
    const ERROR_CODE = 1002;

    /**
     * Сообщение об ошибке
     *
     * @var string
     */
    protected $_message = '';


    /**
     * Установка параметров валидатора
     *
     *
     * @param array $options
     *
     * @return void
     */
    public function setOptions(array $options = array())
    {
        //нет дополнительных параметров валидатора
    }

    /**
     * Проверка валидности значения
     *
     *
     * @param mixed    $value Значение для валидации
     *
     * @return bool
     */
    public function valid($value)
    {
        if (!is_string($value) && !is_int($value)) {
            $this->_message = 'Value ' . $value . ' does not appear to be an integer';
            return false;
        }

        if (!preg_match('/^\d+$/', strval($value))) {
            $this->_message = 'Value ' . $value . ' does not appear to be an integer';
            return false;
        }

        return true;
    }

    /**
     * Получение кода ошибки валидации
     *
     * @return int
     */
    public function getErrorCode()
    {
        return self::ERROR_CODE;
    }

    /**
     * Получение сообщения об ошибке валидации
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_message;
    }
}
