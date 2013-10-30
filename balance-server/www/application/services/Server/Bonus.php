<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.09.12
 * Time: 17:56
 *
 * Сервис зачисления бонусов пользователям
 */
class App_Service_Server_Bonus
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
     * Объект модели соц. сети
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
     * Создание объекта
     *
     * @param string|null $idServiceUser Идентификатор пользователя в соц. сети
     * @param string|null $nameService   Наименование соц. сети
     */
    public function __construct($idServiceUser = null, $nameService = null)
    {
        $this->setIdServiceUser($idServiceUser);
        $this->setNameService($nameService);
    }

    /**
     * Установка идентификатора пользователя в соц. сети
     *
     * @param string $idServiceUser
     */
    public function setIdServiceUser($idServiceUser)
    {
        $this->_idServiceUser = $idServiceUser;
    }

    /**
     * Получение идентификатора пользователя в соц. сети
     *
     * @return string
     */
    public function getIdServiceUser()
    {
        return $this->_idServiceUser;
    }

    /**
     * Установка наименования соц. сети
     *
     * @param string $nameService
     */
    public function setNameService($nameService)
    {
        $this->_nameService = $nameService;
    }

    /**
     * Получение наименования соц. сети
     *
     * @return string
     */
    public function getNameService()
    {
        return $this->_nameService;
    }

    /**
     * Установка объекта модели соц. сети
     *
     * @param \App_Model_Service $service
     */
    public function setService($service)
    {
        $this->_service = $service;
    }

    /**
     * Получение объекта модели соц. сети
     *
     * @throws Core_Exception
     * @return \App_Model_Service
     */
    public function getService()
    {
        if (null === $this->_service) {
            $service = new App_Model_Service();
            $where = $service->select()->where('name = ?', $this->getNameService());
            if (null == $service->fetchRow($where)->getId()) {
                throw new Core_Exception('Unknown service name ' . $this->getNameService());
            } else {
                $this->setService($service);
            }
        }
        return $this->_service;
    }

    /**
     * Установка объекта модели пользователя
     *
     * @param \App_Model_User $user
     */
    public function setUser($user)
    {
        $this->_user = $user;
    }

    /**
     * Получение объекта модели пользователя
     *
     * @throws Core_Exception
     * @return \App_Model_User
     */
    public function getUser()
    {
        if (null === $this->_user) {
            $user = new App_Model_User();
            $where = $user->select()->where('id_service = ?', $this->getService()->getId())
                ->where('id_service_user = ?', $this->getIdServiceUser());
            if (null == $user->fetchRow($where)->getId()) {
                throw new Core_Exception('Failed to find user ' . $this->getIdServiceUser()
                    . ' in service ' . $this->getService()->getName());
            } else {
                $this->setUser($user);
            }
        }
        return $this->_user;
    }

    /**
     * Зачисление ежечасного бонуса
     *
     * @return int Текущий баланс пользователя
     * @throws Core_Exception
     */
    public function addHourBonus()
    {
        //Проверка активности ежечасного бонуса
        $timer = new App_Service_Server_Timer();
        $timer->setUser($this->getUser());
        if ($timer->getHourBonusRestSeconds() > 0) {
            throw new Core_Exception('Hour bonus not active');
        }

        //Зачисление 50 фишек на счет пользователя
        $this->getUser()->setBalance($this->getUser()->getBalance() + 50);
        if (!$this->getUser()->save()) {
            throw new Core_Exception('Failed to change user balance');
        }

        //Обнуление таймера
        $timer->resetHourBonus();

        //Возвращаем текущий баланс пользователя
        return $this->getUser()->getBalance();
    }

    /**
     * Зачисление ежедневного бонуса
     *
     * @return int Текущий баланс пользователя
     * @throws Core_Exception
     */
    public function addDailyBonus()
    {
        //Проверка активности ежечасного бонуса
        $timer = new App_Service_Server_Timer();
        $timer->setUser($this->getUser());
        if ($timer->getDailyBonusRestSeconds() > 0) {
            throw new Core_Exception('Daily bonus not active');
        }

        //Зачисление 250 фишек на счет пользователя
        $this->getUser()->setBalance($this->getUser()->getBalance() + 250);
        if (!$this->getUser()->save()) {
            throw new Core_Exception('Failed to change user balance');
        }

        //Обнуление таймера
        $timer->resetDailyBonus();

        //Возвращаем текущий баланс пользователя
        return $this->getUser()->getBalance();
    }
}
