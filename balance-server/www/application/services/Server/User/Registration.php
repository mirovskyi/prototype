<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 24.09.12
 * Time: 17:38
 *
 * Класс реализации регистрации пользователя в игровом сервисе
 */
class App_Service_Server_User_Registration
{

    /**
     * Объект данных пользователя
     *
     * @var App_Model_UserInfo
     */
    protected $_userInfo;

    /**
     * Объект модели платформы
     *
     * @var App_Model_Service
     */
    protected $_service;


    /**
     * Создание нового объекта
     *
     * @param string     $nameService Наименование платформы
     * @param array|null $data        Данные пользователя
     * @throws Core_Exception
     */
    public function __construct($nameService = null, $data = null)
    {
        //Получение данных платформы
        if (null !== $nameService) {
            $service = new App_Model_Service();
            $where = $service->select()->where('name = ?', $nameService);
            $service->fetchRow($where);
            if (!$service->getId()) {
                throw new Core_Exception('Unknown service ' . $nameService, 110);
            }
            //Установка объекта модели платформы
            $this->setService($service);
        }

        if (is_array($data)) {
            $this->setOptions($data);
        }
    }

    /**
     * Установка объекта модели платформы
     *
     * @param App_Model_Service $service
     */
    public function setService(App_Model_Service $service)
    {
        $this->_service = $service;
    }

    /**
     * Получение объекта модели платформы
     *
     * @return App_Model_Service
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * Установка регистрационных данных
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if (!is_array($options)) {
            return;
        }

        //Установка данных пользователя
        $this->getUserInfo()->setOptions($options);
    }

    /**
     * Установка объекта данных пользователя
     *
     * @param App_Model_UserInfo $userInfo
     */
    public function setUserInfo(App_Model_UserInfo $userInfo)
    {
        $this->_userInfo = $userInfo;
    }

    /**
     * Получение объекта данных пользователя
     *
     * @return App_Model_UserInfo
     */
    public function getUserInfo()
    {
        if (null === $this->_userInfo) {
            $this->setUserInfo(new App_Model_UserInfo());
        }
        return $this->_userInfo;
    }

    /**
     * Регистрация пользователя
     *
     * @return int
     */
    public function registration()
    {
        //Поверка валидности регистрационных данных
        $this->_valid();

        //Сохранение данных пользователя в БД
        $this->_saveUser();

        //Возвращаем идентификатор созданного пользователя
        return $this->getUserInfo()->getIdUser();
    }

    /**
     * Проверка валидности регистрационных данных
     *
     * @throws Core_Exception
     */
    private function _valid()
    {
        //Проверка наличия обязательных параметров
        if (!$this->getUserInfo()->getLogin() ||
                !$this->getUserInfo()->getPasswd() ||
                !$this->getUserInfo()->getEmail()) {
            //Не все обязательные параметры
            throw new Core_Exception('Not all required parameters are received', 638);
        }

        //Проверка валидности email
        $emailValidate = new Zend_Validate_EmailAddress(array(
            'domain' => false,
            'allow' => Zend_Validate_Hostname::ALLOW_ALL
        ));
        if (!$emailValidate->isValid($this->getUserInfo()->getEmail())) {
            throw new Core_Exception('Invalid email param', 637);
        }

        //Проверка наличия логина в базе
        $userInfo = new App_Model_UserInfo();
        $where = $userInfo->select()->where('login = ?', $this->getUserInfo()->getLogin());
        $userInfo->fetchRow($where);
        if ($userInfo->getId()) {
            //Дублирующий логин
            throw new Core_Exception('Duplicate login', 5001);
        }
    }

    /**
     * Сохранение данных пользователя в БД
     *
     * @throws Core_Exception
     */
    private function _saveUser()
    {
        //Генерация соли хеша пароля
        $this->getUserInfo()->setSalt(uniqid());
        //Получение хеш пароля с солью
        $md5Passwd = $this->getUserInfo()->initHashPassword($this->getUserInfo()->getPasswd());
        //Установка пароля
        $this->getUserInfo()->setPasswd($md5Passwd);

        //Сохранение данных пользователя
        if (!$this->getUserInfo()->save()) {
            throw new Core_Exception('Error writing to database', 5000);
        }

        //Стартуем транзакцию
        $this->getUserInfo()->getMapper()->getDbTable()->getAdapter()->beginTransaction();

        //Создание записи пользователя
        $user = new App_Model_User();
        $user->setIdService($this->getService()->getId());
        $user->setIdServiceUser($this->getUserInfo()->getId());
        //TODO: установка начального баланса пользователя
        $user->setBalance(500);
        //Сохранение данных пользвателя
        if (!$user->save()) {
            //Завершение транзакции
            $this->getUserInfo()->getMapper()->getDbTable()->getAdapter()->rollBack();
            throw new Core_Exception('Error writing to database', 5000);
        }

        //Запись идентификатора пользователя в данные пользователя
        $this->getUserInfo()->setIdUser($user->getId());
        //Сохранение изменений
        if (!$this->getUserInfo()->save()) {
            //Откат транзакции
            $this->getUserInfo()->getMapper()->getDbTable()->getAdapter()->rollBack();
            throw new Core_Exception('Error writing to database', 5000);
        }

        //Коммит транзакции
        $this->getUserInfo()->getMapper()->getDbTable()->getAdapter()->commit();
    }

}
