<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.09.12
 * Time: 15:55
 *
 * Сервис получения данных таймеров активации событий для пользователя
 */
class App_Service_Server_Timer
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
     * Объект модели таймеров пользователя
     *
     * @var App_Model_UserTimer
     */
    protected $_userTimer;


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
     * Установка объекта модели таймеров пользователя
     *
     * @param App_Model_UserTimer $timer
     */
    public function setUserTimer(App_Model_UserTimer $timer)
    {
        $this->_userTimer = $timer;
    }

    /**
     * Получение объекта модели таймеров пользователя
     *
     * @return App_Model_UserTimer
     */
    public function getUserTimer()
    {
        if (null === $this->_userTimer) {
            $timer = new App_Model_UserTimer();
            $where = $timer->select()->where('id_user = ?', $this->getUser()->getId());
            if (null == $timer->fetchRow($where)->getId()) {
                //Создание новой пустой записи с таймерами пользователя
                $timer->setIdUser($this->getUser()->getId())
                      ->save();
            }
            $this->setUserTimer($timer);
        }
        return $this->_userTimer;
    }

    /**
     * Получение остатка времени до активации часового бонуса (в секундах)
     *
     * @return int
     */
    public function getHourBonusRestSeconds()
    {
        return $this->_getRestSeconds($this->getUserTimer()->getHourBonus(), 3600);
    }

    /**
     * Обнуление таймера почасового бонуса
     *
     * @return bool|int
     */
    public function resetHourBonus()
    {
        return $this->getUserTimer()->setHourBonus(time())->save();
    }

    /**
     * Получение остатка времени до активации ежедневного бонуса (в секундах)
     *
     * @return int
     */
    public function getDailyBonusRestSeconds()
    {
        return $this->_getRestSeconds($this->getUserTimer()->getDailyBonus(), 3600 * 24);
    }

    /**
     * Обнуление таймера дневного бонуса
     *
     * @return bool|int
     */
    public function resetDailyBonus()
    {
        return $this->getUserTimer()->setDailyBonus(time())->save();
    }

    /**
     * Получение остатка времени до возможности дарить падарок другу (в секундах)
     *
     * @return int
     */
    public function getFriendPresentRestSeconds()
    {
        return $this->_getRestSeconds($this->getUserTimer()->getFriendPresent(), 3600 * 24);
    }

    /**
     * Обнуление таймера возможности подарка другу
     *
     * @return bool|int
     */
    public function resetFriendPresent()
    {
        return $this->getUserTimer()->setFriendPresent(time())->save();
    }

    /**
     * Получение массива данных таймеров пользователя
     *
     * @return array
     */
    public function getDataInArray()
    {
        //Отдаем массив данных таймеров пользователя
        return array(
            'hourBonus' => $this->getHourBonusRestSeconds(),
            'dailyBonus' => $this->getDailyBonusRestSeconds(),
            'friendPresent' => $this->getFriendPresentRestSeconds()
        );
    }

    /**
     * Получение остатка времени до активации события
     *
     * @param int $lastActivateTime Время в Unix формате
     * @param int $eventCycle       Цикл активации события в секундах
     *
     * @return int
     */
    private function _getRestSeconds($lastActivateTime, $eventCycle)
    {
        //Получаем время активации бонуса
        $activeTime = $lastActivateTime + ($eventCycle);
        //Получение остатка времени до активации
        $restTime = $activeTime - time();
        //Если значение отрицательное - время активации уже прошло
        if ($restTime < 0) {
            $restTime = 0;
        }

        //Возвращаем время, оставшееся до активации события
        return $restTime;
    }
}
