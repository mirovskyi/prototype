<?php

/**
 * Description of User
 *
 * @author aleksey
 */
class Core_Social_User
{
    
    /**
     * Системное имя социальной сети
     *
     * @var string
     */
    protected $_network;
    
    /**
     * Идентификатор пользователя в социальной сети
     *
     * @var string
     */
    protected $_id;
    
    /**
     * Имя пользователя
     * 
     * @var string 
     */
    protected $_name;

    /**
     * URL фото пользователя в соц. сети
     *
     * @var string
     */
    protected $_photo;
    
    /**
     * Параметры пользователя из соц. сети
     *
     * @var array 
     */
    protected $_params;
    
    
    /**
     * Создания объекта данных пользователя
     *
     * @param string|null $network Системное имя социальной сети
     * @param array|null $userParams Парамктры пользователя из соц. сети
     */
    public function __construct($network = null, $userParams = null)
    {
        $this->setNetwork($network);
        
        //if (null !== $userParams) {
        if (is_array($userParams)) {
            $this->setParams($userParams);
        }
    }

    /**
     * Magic method __set
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws Core_Social_Exception
     */
    public function __set($name, $value) 
    {
        $methodName = 'set' . ucfirst($name);
        if (method_exists($this, $methodName)) {
            $this->$methodName($value);
        } else {
            throw new Core_Social_Exception('Unknown method call \'' . $methodName 
                                            . '\' in ' . get_class($this));
        }
    }

    /**
     * Magic method __get
     *
     * @param string $name
     *
     * @throws Core_Social_Exception
     * @return mixed
     */
    public function __get($name) 
    {
        $methodName = 'get' . ucfirst($name);
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        } else {
            throw new Core_Social_Exception('Unknown method call \'' . $methodName 
                                            . '\' in ' . get_class($this));
        }
    }
    
    /**
     * Установка системного имени социальной сети пользователя
     *
     * @param string $name
     * @return Core_Social_User 
     */
    public function setNetwork($name)
    {
        $this->_network = $name;
        return $this;
    }
    
    /**
     * Получение системного имени социальной сети пользователя
     *
     * @return string
     */
    public function getNetwork()
    {
        return $this->_network;
    }
    
    /**
     * Установка идентификатора пользователя в соц. сети
     *
     * @param string $id
     * @return Core_Social_User
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }
    
    /**
     * Получение идентификатора пользователя в соц. сети
     *
     * @return string 
     */
    public function getId()
    {
        return $this->_id;
    }
    
    /**
     * Установка имени пользователя
     *
     * @param string $name
     * @return Core_Social_User
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }
    
    /**
     * Получение имени пользователя
     *
     * @return string 
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Установка URL адреса фотографии пользователя в соц. сети
     *
     * @param string $photo
     * @return Core_Social_User
     */
    public function setPhoto($photo)
    {
        $this->_photo = $photo;
        return $this;
    }

    /**
     * Получение URL адреса фотографии пользователя в соц. сети
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->_photo;
    }

    /**
     * Получение ключа доступа к URL фото пользователя
     *
     * @return string
     */
    public function getPhotoKey()
    {
        return $this->getNetwork() . '_' . $this->getId();
    }

    /**
     * Получение URL фотографии пользователя
     *
     * @return string
     */
    public function getPhotoUrl()
    {
        return Core_Plugin_UserPhoto::getPhotoUrl($this->getPhotoKey());
    }
    
    /**
     * Установка параметров пользователя из соц. сети
     *
     * @param array $params
     * @return Core_Social_User
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
        //Проверка наличия конфигов для данной сети
        if (Core_Social_Config::has($this->getNetwork())) {
            //Получаем конфиги социальной сети
            $config = Core_Social_Config::get($this->getNetwork());
            //Проверка наличия правил преобразования данных пользователя
            if (isset($config->user)) {
                //Конфиги данных пользователя
                $userConfig = $config->user->toArray();
                //Проверка необходимости дополнительно получать данные пользователя соц. сети
                if (isset($userConfig['_init'])) {
                    //Дополняем параметры
                    $initParamsImpl = $this->_getInitParamsImpl($userConfig['_init']);
                    if ($initParamsImpl) {
                        $initParamsImpl->initUserInfo($this->_params, $config->toArray());
                    }
                    //Удаление ключа из конфигов
                    unset($userConfig['_init']);
                }
                //Преобразовываем данные из соц. сети в данные объекта пользователя
                foreach($userConfig as $objectKey => $socialKey) {
                    //Проверка формата параметра (составной или нет)
                    if (is_array($socialKey)) {
                        $params = array();
                        foreach($socialKey as $key) {
                            if ($this->hasParam($key)) {
                                $params[] = $this->getParam($key);
                            }
                        }
                        $param = implode(' ', $params);
                    } else {
                        $param = $this->getParam($socialKey);
                    }
                    //Установка параметра пользователя
                    $this->__set($objectKey, $param);
                }
            }
            //Проверка наличия идентификатора пользователя
            //TODO: Разкоментить на реальной
            /*if (null === $this->getId()) {
                throw new Core_Social_Exception('Invalid request data to create a user session', 102);
            }*/

            //
        }
        
        return $this;
    }
    
    /**
     * Получение параметров пользователя из соц. сети
     *
     * @return array 
     */
    public function getParams()
    {
        return $this->_params;
    }
    
    /**
     * Получение значения параметра пользователя
     *
     * @param string $name
     * @param mixed $default
     * @return mixed 
     */
    public function getParam($name, $default = null)
    {
        if ($this->hasParam($name)) {
            return $this->_params[$name];
        } else {
            return $default;
        }
    }
    
    /**
     * Проверка наличия параметра пользователя
     *
     * @param string $name
     * @return bool 
     */
    public function hasParam($name)
    {
        return isset($this->_params[$name]);
    }

    /**
     * Получение объекта реализующего интерфейс инициализции данных пользователя
     *
     * @param $className
     *
     * @return Core_Social_User_InfoInterface|bool
     */
    private function _getInitParamsImpl($className)
    {
        if (class_exists($className)) {
            $object = new $className();
            if ($object instanceof Core_Social_User_InfoInterface) {
                return $object;
            }
        }

        return false;
    }
    
}