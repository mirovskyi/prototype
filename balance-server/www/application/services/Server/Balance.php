<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 15.02.12
 * Time: 11:07
 *
 * Класс модели работы с балансами пользователей
 */
class App_Service_Server_Balance
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
     * Установка объекта модели пользователя
     *
     * @param App_Model_User $user
     * @return App_Service_Server_Balance
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
            //Получение объекта данных пользователя
            $info = new App_Service_Server_Info();
            $user = $info->getUserInfo($this->_idServiceUser, $this->_nameService);
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
