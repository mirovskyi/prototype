<?php

/**
 * Description of Storage
 *
 * @author aleksey
 */
class App_Model_Mapper_Storage implements App_Model_Mapper_Interface
{
    
    /**
     * Объект реализации работы с хранилищем
     *
     * @var Core_Storage_Implementation_Interface 
     */
    protected $_implementation;


    /**
     * Установка объекта реализации работы с хранилищем
     *
     * @param Core_Storage_Implementation_Interface $implementation
     * @param array $options [optional]
     * @throws Core_Exception
     * @return App_Model_Mapper_Storage
     */
    public function setImplementation($implementation, array $options = array())
    {
        if ($implementation instanceof Core_Storage_Implementation_Interface) {
            $this->_implementation = $implementation;
        } elseif (is_string($implementation)) {
            $this->_implementation = Core_Storage::factory($implementation, $options);
        } else {
            throw new Core_Exception('Unknown storage implementation type');
        }
        return $this;
    }
    
    /**
     * Получение объекта реализации работы с хранилищем
     *
     * @return Core_Storage_Implementation_Interface 
     */
    public function getImplementation()
    {
        if (!$this->_implementation instanceof Core_Storage_Implementation_Interface) {
            $this->setImplementation(Core_Storage::factory());
        }
        return $this->_implementation;
    }
    
    /**
     * Сохранение данных модели в хранилище
     *
     * @param App_Model_Interface $model
     * @return bool 
     */
    public function save(App_Model_Interface $model)
    {
        $key = $model->getKey();
        if ($key) {
            return $this->getImplementation()->set($key, clone($model)); //Клонирование объекта модели для избежания модиикации данных объекта методом __sleep
        } else {
            return false;
        }
    }
    
    /**
     * Поиск данных модели по ключу в хранилище
     *
     * @param string $key
     * @param App_Model_Interface $model
     * @return bool
     */
    public function find($key, App_Model_Interface $model)
    {
        $data = $this->getImplementation()->get($key);
        if (!$data) {
            //Zend_Registry::get('log')->debug('NOT FOUND KEY: ' . $key);
        }
        if ($data && $data instanceof $model) {
            //Рефлексия объекта модели
            $r = new ReflectionObject($model);
            //Передача данных объекту модели через магические методы __get, __set
            foreach($r->getProperties() as $property) {
                //Имя своиства
                $name = str_replace('_', '', $property->getName());
                //Пропускаем mapper
                if ($name == 'mapper') {
                    continue;
                }
                //Копирование данных моделей
                $model->$name = $data->$name;
            }

            return true;
        }
        return false;
    }

    /**
     * Создание блокировки для сессии
     *
     * @param App_Model_Interface $model
     * @throws Core_Exception
     */
    public function lock(App_Model_Interface $model)
    {
        //Получаем идентификатор сессии
        $key = $model->getKey();

        //Создание записи блокировки
        if ($key) {
            $this->getImplementation()->lock($key);
        }
    }

    /**
     * Снятие блокировки сессии
     *
     * @param App_Model_Interface $model
     * @throws Core_Exception
     */
    public function unlock(App_Model_Interface $model)
    {
        //Получаем идентификатор сессии
        $key = $model->getKey();
        //Удаление записи блокировки
        if ($key) {
            $this->getImplementation()->unlock($key);
        }
    }

    /**
     * Проверка блокировки данных модели
     * В случае передачи параметра pid, проверяется блокировка данных модели указанным процессом
     *
     * @param App_Model_Interface $model
     * @param string|null $pid
     * @return bool
     */
    public function isLock(App_Model_Interface $model, $pid = null)
    {
        //Получаем идентификатор сессии
        $key = $model->getKey();

        if (null !== $pid) {
            //Проверка блокировки данных указанным процессом
            return $this->getImplementation()->getLockPid($key) == $pid;
        } else {
            //Проверка блокировки данных
            return $this->getImplementation()->isLocked($key);
        }
    }
    
    /**
     * Удаление данных модели из хранилища
     *
     * @param App_Model_Interface $model
     * @return bool 
     */
    public function delete(App_Model_Interface $model)
    {
        $key = $model->getKey();
        if ($key) {
            return $this->getImplementation()->delete($key);
        } else {
            return false;
        }
    }
}