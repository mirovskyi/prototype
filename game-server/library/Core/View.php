<?php
 
class Core_View
{

    /**
     * Параметры передаваемые в шаблон для формирования контента
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Путь к директории с шаблонами
     *
     * @var string
     */
    protected $_templateDirectory;

    /**
     * Пути к директориям помошников вида
     *
     * @var array
     */
    protected $_helperPaths = array();

    /**
     * Список декораторов
     *
     * @var array
     */
    protected $_decorators = array();

    /**
     * Имя файла шаблона
     *
     * @var string
     */
    protected $_template;

    /**
     * Массив объектов помошников вида
     *
     * @var array
     */
    private $_helpers = array();


    /**
     * __construct
     *
     * @param array|null $options
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Magic method __call
     *
     * @param string $name      Имя метода
     * @param array  $arguments Параметры вызываемого метода
     *
     * @return mixed
     * @throws Core_View_Exception
     */
    public function __call($name, $arguments)
    {
        //Формирование имени помошника вида
        $helperName = ucfirst($name);
        //Получение объекта помошника вида
        $helper = $this->_getHelper($helperName);
        if (false === $helper) {
            throw new Core_View_Exception('Helper ' . $name . ' not found in ('
                                          . implode(', ', array_values($this->getHelperPaths())) . ')');
        }

        //Возвращаем результат выполнения действия помошника вида
        return call_user_func_array(array($helper, $name), $arguments);
    }

    /**
     * Получение параметра шаблона
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        } else {
            return null;
        }
    }

    /**
     * Установка параметра шаблона
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_params[$name] = $value;
    }

    /**
     * Проверка наличия параметра
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_params[$name]);
    }

    /**
     * Установка настроек шаблона
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        if (!count($options)) {
            return;
        }
        //Директория шаблонов
        if (isset($options['templateDirectory'])) {
            $this->setTemplateDirectory($options['templateDirectory']);
        }
        //Декораторы
        if (isset($options['decorators'])) {
            $this->setDecorators($options['decorators']);
        }
        //Помошники вида
        if (isset($options['helper']) && is_array($options['helper'])) {
            $this->setHelperPaths($options['helper']);
        }
    }

    /**
     * Получение объекта данных запроса
     *
     * @return Core_Protocol_Request
     */
    public function getRequest()
    {
        return Core_Server::getInstance()->getServer()->getRequest();
    }

    /**
     * Установка пути к директории шаблона
     *
     * @param string $dir
     * @return Core_View
     */
    public function setTemplateDirectory($dir)
    {
        $this->_templateDirectory = $dir;
        return $this;
    }

    /**
     * Получение пути директории шаблона
     *
     * @return string
     */
    public function getTemplateDirectory()
    {
        return $this->_templateDirectory;
    }

    /**
     * Установка списка путей к директориям помошников вида
     *
     * @param array $paths
     *
     * @return Core_View
     */
    public function setHelperPaths(array $paths)
    {
        $this->_helperPaths = array();
        foreach($paths as $prefix => $path) {
            $this->addHelperPath($path, $prefix);
        }
        return $this;
    }

    /**
     * Добавление пути директории помошников вида
     *
     * @param string $path   Путь к директории
     * @param string $prefix Префикс имени класса помошника вида
     *
     * @return Core_View
     */
    public function addHelperPath($path, $prefix = 'Core_View_Helper')
    {
        if ($prefix[strlen($prefix) - 1] != '_') {
            $prefix .= '_';
        }
        $this->_helperPaths[$prefix] = $path;
        return $this;
    }

    /**
     * Получение списка путей к директориям помошников вида
     *
     * @return array
     */
    public function getHelperPaths()
    {
        return $this->_helperPaths;
    }

    /**
     * Установка имени файла шаблона
     *
     * @param string $filename
     * @return Core_View
     */
    public function setTemplate($filename)
    {
        $this->_template = $filename;
        return $this;
    }

    /**
     * Получение имени файла шаблона
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * Установка списка декораторов
     *
     * @param array|string $decorators
     */
    public function setDecorators($decorators)
    {
        foreach((array) $decorators as $decorator) {
            $this->addDecorator($decorator);
        }
    }

    /**
     * Получение списка декораторов
     *
     * @return array
     */
    public function getDecorators()
    {
        return $this->_decorators;
    }

    /**
     * Добавление декоратора
     *
     * @param string $decorator
     * @return Core_View
     */
    public function addDecorator($decorator)
    {
        if (!in_array($decorator, $this->_decorators)) {
            $this->_decorators[] = $decorator;
        }
        return $this;
    }

    /**
     * Удаление декоратора
     *
     * @param $decorator
     * @return Core_View
     */
    public function delDecorator($decorator)
    {
        $index = array_search($decorator, $this->_decorators);
        if (false !== $index) {
            unset($this->_decorators[$index]);
        }
        return $this;
    }

    /**
     * Установка параметра(ов) шаблона
     *
     * @throws Core_View_Exception
     * @param string|array $param
     * @param mixed|null $value
     * @return void
     */
    public function assign($param, $value = null)
    {
        if (is_array($param)) {
            foreach($param as $key => $value) {
                $this->_params[$key] = $value;
            }
        } elseif (is_string($param)) {
            $this->_params[$param] = $value;
        } else {
            throw new Core_View_Exception('Incorrect param key type for assign to template');
        }
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
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        } else {
            return $default;
        }
    }

    /**
     * Получение контента шаблона
     *
     * @throws Core_View_Exception
     * @param string|null $template
     * @return string
     */
    public function render($template = null)
    {
        //Декорируем объект вида
        $view = $this;
        foreach($this->getDecorators() as $decorator) {
            if (class_exists($decorator)) {
                $decorator = new $decorator($view);
                if ($decorator instanceof Core_View_Decorator_Abstract) {
                    $view = $decorator;
                }
            }
        }

        //Формирование отображения
        return $view->_render($template);
    }

    /**
     * Формирование контента шаблона
     *
     * @param string|null $template
     * @return string
     * @throws Core_View_Exception
     */
    protected function _render($template = null)
    {
        //Получаем путь к файлу шаблона
        $filename = $this->_getTemplatePath($template);
        //Проверка наличичя файла шаблона
        if (!file_exists($filename)) {
            throw new Core_View_Exception('Can`t find template file ' . $filename);
        }

        //Формирование контента
        ob_start();
        include $filename;
        $content = ob_get_contents();
        ob_end_clean();

        //Возвращаем контент
        return $content;
    }

    /**
     * Получение полного пути к файлу шаблона
     *
     * @param string|null $template
     * @return string|null
     */
    protected function _getTemplatePath($template = null)
    {
        //Путь к файлу шаблона
        if (null !== $template) {
            if ($template[0] == '/' || $template[0] == '\\') {
                return $template;
            }
        } else {
            $template = $this->getTemplate();
        }
        //Полный путь к файлу шаблона
        return $this->getTemplateDirectory() . DIRECTORY_SEPARATOR
               . $template;
    }

    /**
     * Получение объекта помошника вида
     *
     * @param string $name Имя помошника вида
     *
     * @return Core_View_Helper_Abstract|bool
     */
    protected function _getHelper($name)
    {
        //Проверка наличия объекта помошника вида
        foreach($this->_helpers as $helper) {
            //Проверка соответсвия объекта вызываемому помошнику вида
            if (preg_match('/_' . $name . '$/i', get_class($helper))) {
                //Возвращаем объект помошника вида
                return $helper;
            }
        }

        //Поиск файла класса помошника вида
        foreach($this->getHelperPaths() as $prefix => $path) {
            $filename = $path . DIRECTORY_SEPARATOR . $name . '.php';
            $filename = str_replace('//', '/', $filename);
            //Проверка наличия файла
            if (is_file($filename)) {
                //Подключение класса помошника вида
                require_once $filename;
                //Инициализация объекта помошника
                $className = $prefix . $name;
                if (!class_exists($className)) {
                    continue;
                }
                $helper = new $className();
                //Добавление объекта в список инициализированных помошников вида
                $this->_helpers[] = $helper;
                //Возвращаем объект помошника вида
                return $helper;
            }
        }

        //Помошник вида не найден
        return false;
    }

}
