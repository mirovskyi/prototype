<?php

/**
 * Description of File
 *
 * @author aleksey
 */
class Core_Storage_Implementation_File implements Core_Storage_Implementation_Interface 
{
    
    /**
     * Путь к директории хранилища
     * 
     * @var string
     */
    protected $_path;
    
    /**
     * __construct
     *
     * @param array $options [optional] Настройки соединения с хранилищем данных
     */
    public function __construct($options = null)
    {
        $this->setPath(APPLICATION_PATH . '/../data/storage');
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
    
    /**
     * Установка настроек
     * 
     * @param array $options 
     */
    public function setOptions(array $options) 
    {
        foreach($options as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }
    }
    
    /**
     * Установка пути к директории хранилища
     * 
     * @param string $path
     * @return Core_Storage_File 
     */
    public function setPath($path)
    {
        $this->_path = $path;
        return $this;
    }
    
    /**
     * Получение пути к директории хранилища
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }
    
    /**
     * Метод получения данных из хранилища
     * 
     * @param string $key
     * @return mixed 
     */
    public function get($key)
    {
        $filename = $this->getPath() . '/' . $key;
        $data = @file_get_contents($filename);
        if (!$data) {
            throw new Exception('Can not get data from storge', 1020);
        }
        return $data;
    }
    
    /**
     * Метод установки данных в хранилище
     * 
     * @param string $key
     * @param mixed $value
     * @return boolean 
     */
    public function set($key, $value)
    {
        //Путь к файлу
        $filename = $this->getPath() . '/' . $key;
        //Если сохраняются данные игры увеличиваем порядковый номер команды
        if ($value instanceof Core_Game_Abstract) {
            $value->incCommand();
        }
        //Сохраняем данные
        if (!@file_put_contents($filename, $value)) {
            //Если была попытка сохранения игры, возвращаем начальный порядковый номер команды
            if ($value instanceof Core_Game_Abstract) {
                $value->setCommand($value->getCommand() - 1);
            }
            return false;
        }
        return true;
    }
    
}