<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 21.03.12
 * Time: 11:14
 *
 * Абстракция декоратора шаблона вида
 */
abstract class Core_View_Decorator_Abstract extends Core_View
{

    /**
     * Объект вида
     *
     * @var Core_View
     */
    protected $_view;


    /**
     * Создание нового декоратора
     *
     * @param Core_View $view
     */
    public function __construct(Core_View $view)
    {
        $this->_view = $view;
    }

    /**
     * Получение параметра шаблона
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_view->__get($name);
    }

    /**
     * Проверка наличия параметра
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->_view->__isset($name);
    }

    /**
     * Получение значения параметра шаблона
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->_view->get($name, $default);
    }

    /**
     * Получение контента шаблона
     *
     * @throws Core_View_Exception
     * @param string|null $template
     * @return string
     */
    protected function _render($template = null)
    {
        return $this->_view->_render($template);
    }

}
