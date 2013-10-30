<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 13.03.12
 * Time: 14:48
 *
 * Модель "мусорника" данных хранилища
 */
class App_Model_Trash implements App_Model_Interface
{

    /**
     * Пространство имен для данных игр
     */
    const GAME_NAMESPACE = 'game';


    /**
     * Пространства имен
     *
     * @var string
     */
    protected $_namespace;

    /**
     * Список ключей данных для удаления
     *
     * @var array
     */
    protected $_items = array();

    /**
     * Модель доступа к данным хранилица
     *
     * @var App_Model_Mapper_Storage
     */
    protected $_mapper;


    /**
     * Создание объекта модели "мусорки"
     *
     * @param string $namespace
     */
    public function __construct($namespace = '')
    {
        $this->setNamespace($namespace);
    }

    /**
     * Метод установки параметров модели
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        if (is_array($options) && count($options) > 0) {
            foreach($options as $name => $value) {
                $this->__set($name, $value);
            }
        }
    }

    /**
     * Магический метод __get
     *
     * @param string $name
     * @throws Exception
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            throw new Exception('Unknown method ' . $method . ' called in ' . get_class($this));
        }
    }

    /**
     * Магический метод __set
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            $this->$method($value);
        }
    }

    public function __sleep()
    {
        return array('_namespace','_items');
    }

    /**
     * Получение ключа записи в хранилище
     *
     * @return string
     */
    public function getKey()
    {
        return 'trash:' . $this->getNamespace();
    }

    /**
     * Установка правстранства имен
     *
     * @param string $namespace
     * @return App_Model_Trash
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
        return $this;
    }

    /**
     * Получение пространства имен
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Установка записей
     *
     * @param array $items
     * @return App_Model_Trash
     */
    public function setItems($items)
    {
        if (is_array($items)) {
            $this->_items = $items;
        }
        return $this;
    }

    /**
     * Получение записей
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Добавление записи
     *
     * @param mixed $item
     */
    public function addItem($item)
    {
        $this->_items[] = $item;
    }

    /**
     * Удаление записи
     *
     * @param mixed $item
     */
    public function delItem($item)
    {
        $key = array_search($item, $this->_items);
        if (false !== $key) {
            unset($this->_items[$key]);
        }
    }

    /**
     * Установка объекта модели доступа к данным хранилища
     *
     * @param App_Model_Mapper_Interface $mapper
     * @return App_Model_Trash
     */
    public function setMapper(App_Model_Mapper_Interface $mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Получение объекта модели доступа к данным хранилища
     *
     * @return App_Model_Mapper_Interface
     */
    public function getMapper()
    {
        if (!$this->_mapper instanceof App_Model_Mapper_Interface) {
            $this->setMapper(new App_Model_Mapper_Storage());
        }
        return $this->_mapper;
    }

    /**
     * Поиск данных модели
     *
     * @param string $key
     * @return App_Model_Interface
     */
    public function find($key)
    {
        //Поиск данных по указанному ключу
        $this->getMapper()->find($key, $this);
        return $this;
    }

    /**
     * Обновление данных модели
     *
     * @return App_Model_Trash
     */
    public function update()
    {
        $this->find($this->getKey());
        return $this;
    }

    /**
     * Сохранение данных модели
     *
     * @return bool
     */
    public function save()
    {
        return $this->getMapper()->save($this);
    }

    /**
     * Удаление данных модели
     *
     * @return bool
     */
    public function delete()
    {
        return $this->getMapper()->delete($this);
    }

    /**
     * Блокировка данных модели
     */
    public function lock()
    {
        $this->getMapper()->lock($this);
    }

    /**
     * Разблокировка данных модели
     */
    public function unlock()
    {
        $this->getMapper()->unlock($this);
    }

    /**
     * Проверка блокировки данных модели
     * В случае передачи параметра pid, проверяется блокировка данных модели указанным процессом
     *
     * @param string|null $pid
     * @return bool
     */
    public function isLock($pid = null)
    {
        return $this->getMapper()->isLock($this, $pid);
    }

    /**
     * Блокировка записи и получение актуальных данных модели
     */
    public function lockAndUpdate()
    {
        //Блокировка данных модели
        $this->lock();
        //Обновление данных
        $this->update();
    }

    /**
     * Сохранение и разблокировка данных модели
     */
    public function saveAndUnlock()
    {
        //Сохранение данных модели
        $this->save();
        //Разблокировка данных
        $this->unlock();
    }

    /**
     * Magic method __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getKey();
    }
}
