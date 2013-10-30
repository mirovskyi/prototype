<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 15.02.12
 * Time: 11:07
 *
 * Класс модели работы с балансами пользователей
 */
class App_Service_Balance
{

    /**
     * Идентификатор пользователя в соц. сети
     *
     * @var string
     */
    protected $_idServiceUser;

    /**
     * Имя сервиса соц. сети
     *
     * @var string
     */
    protected $_nameService;

    /**
     * Объект модели сервиса соц. сети
     *
     * @var App_Model_Service
     */
    protected $_service;

    /**
     * Объект модели пользователя
     *
     * @var App_Model_User
     */
    protected $_user;


    /**
     * Создание нового объекта
     *
     * @param string $idServiceUser Идентификатр пользователя в соц. сети
     * @param string $nameService Имя сервиса соц. сети
     */
    public function __construct($idServiceUser, $nameService)
    {
        $this->_idServiceUser = $idServiceUser;
        $this->_nameService = $nameService;
    }

    /**
     * Установка объекта модели сервиса соц. сети
     *
     * @param App_Model_Service $service
     * @return App_Service_Balance
     */
    public function setService(App_Model_Service $service)
    {
        $this->_service = $service;
        return $this;
    }

    /**
     * Получение объекта модели сервиса соц. сети
     *
     * @return App_Model_Service
     */
    public function getService()
    {
        if (null === $this->_service) {
            //Создание объекта модели сервиса
            $service = new App_Model_Service();
            //Поиск записи по имени сервиса соц. сети
            $where = $service->select()->where('name = ?', $this->_nameService);
            $service->fetchRow($where);
            //Проверка наличия сервиса в базе
            if (!$service->getId()) {
                throw new Zend_Exception('Social network "' . $this->_nameService
                                         . '" does not register in balance service');
            }
            //Установка объекта модели сервиса
            $this->setService($service);
        }

        return $this->_service;
    }

    /**
     * Установка объекта модели пользователя
     *
     * @param App_Model_User $user
     * @return App_Service_Balance
     */
    public function setUser(App_Model_User $user)
    {
        $this->_user = $user;
        return $this;
    }

    /**
     * Получение объекта модели пользователя
     *
     * @return App_Model_User
     */
    public function getUser()
    {
        if (null === $this->_user) {
            //Создание объекта пользователя
            $user = new App_Model_User();
            //Поиск записи по идентификатору пользователя в соц. сети и идентификатору сервиса соц. сети
            $where = $user->select()
                          ->where('id_service = ?', $this->getService()->getId())
                          ->where('id_service_user = ?', $this->_idServiceUser);
            $user->fetchRow($where);
            //Установка объекта модели пользователя
            $this->setUser($user);
        }

        return $this->_user;
    }

    /**
     * Получение текущего баланса пользователя
     *
     * @return int
     */
    public function getUserBalance()
    {
        //Проверка нличия пользователя в базе
        if (!$this->getUser()->getId()) {
            //Создание записи в базе для данного пользователя
            $this->getUser()->setIdService($this->getService()->getId())
                            ->setIdServiceUser($this->_idServiceUser)
                            //TODO: реализовать логику определения начального баланса пользователя
                            ->setBalance(500)
                            ->save();
        }

        //Возвращаем текущий баланс пользователя
        return $this->getUser()->getBalance();
    }

    /**
     * Увеличение баланса пользователя на указанную сумму
     *
     * @param int $amount
     * @return bool|int
     * @throws Zend_Exception
     */
    public function increaseUserBalance($amount)
    {
        //Проверка наличия пользователя в базе
        if (!$this->getUser()->getId()) {
            throw new Zend_Exception('User does not exists');
        }

        //Пополнение баланса пользователя
        $this->getUser()->setBalance($this->getUser()->getBalance() + $amount);
        //Сохраняем данные пользователя
        return $this->getUser()->save();
    }

    /**
     * Уменьшение баланса пользователя на указанную сумму
     *
     * @param int $amount Сумма списания
     * @return bool|int
     * @throws Zend_Exception
     */
    public function reduceUserBalance($amount)
    {
        //Проверка наличия пользователя в базе
        if (!$this->getUser()->getId()) {
            throw new Zend_Exception('User does not exists');
        }

        //Проверка соответствия баланса пользователя
        if ($this->getUser()->getBalance() < $amount) {
            throw new Zend_Exception('Insufficient funds on the user balance');
        }

        //Снимаем средства с баланса пользователя
        $this->getUser()->setBalance($this->getUser()->getBalance() - $amount);
        //Сохраняем данные пользователя
        return $this->getUser()->save();
    }
}
