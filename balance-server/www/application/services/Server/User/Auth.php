<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 24.09.12
 * Time: 19:23
 *
 * Сервис аутентификации пользователя
 */
class App_Service_Server_User_Auth
{

    /**
     * Объект модели данных платформы (сервиса соц. сети)
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
     * Объект модели данных пользователя
     *
     * @var App_Model_UserInfo
     */
    protected $_userInfo;

    /**
     * Объект модели сесси пользователя
     *
     * @var App_Model_UserSession
     */
    protected $_userSession;


    /**
     * Создание нового объекта
     *
     * @param string|null $nameService Наименование платформы (соц. сети)
     * @throws Core_Exception
     */
    public function __construct($nameService = null)
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
    }

    /**
     * Установка объекта модели платформы
     *
     * @param App_Model_Service $service
     */
    public function setService($service)
    {
        $this->_service = $service;
    }

    /**
     * Установка объекта модели платформы
     *
     * @return App_Model_Service
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * Устанвока объекта модели пользователя
     *
     * @param App_Model_User $user
     */
    public function setUser(App_Model_User $user)
    {
        $this->_user = $user;
    }

    /**
     * Получение объекта модели пользователя
     *
     * @return App_Model_User
     */
    public function getUser()
    {
        if (null === $this->_user && $this->getUserSession()) {
            //Получение идентификатора пользователя
            $idUser = $this->getUserSession()->getIdUser();
            if ($idUser) {
                //Создание объекта пользователя
                $user = new App_Model_User();
                $user->find($idUser);
                //Установка объекта пользователя
                $this->setUser($user);
            }
        }
        return $this->_user;
    }

    /**
     * Устанвока объекта модели данных пользователя
     *
     * @param App_Model_UserInfo $userInfo
     */
    public function setUserInfo(App_Model_UserInfo $userInfo)
    {
        $this->_userInfo = $userInfo;
    }

    /**
     * Получение объекта модели данных пользователя
     *
     * @return App_Model_UserInfo
     */
    public function getUserInfo()
    {
        if (null === $this->_userInfo && $this->getUserSession()) {
            //Получение идентификатора пользователя
            $idUser = $this->getUserSession()->getIdUser();
            if ($idUser) {
                //Создание объекта данных пользователя
                $userInfo = new App_Model_UserInfo();
                $where = $userInfo->select()->where('id_user = ?', $idUser);
                $userInfo->fetchRow($where);
                //Установка объекта данных пользователя
                $this->setUserInfo($userInfo);
            }
        }
        return $this->_userInfo;
    }

    /**
     * Установка объекта модели сессии пользователя
     *
     * @param App_Model_UserSession $session
     */
    public function setUserSession(App_Model_UserSession $session)
    {
        $this->_userSession = $session;
    }

    /**
     * Получение объекта модели сессии пользователя
     *
     * @return App_Model_UserSession
     */
    public function getUserSession()
    {
        return $this->_userSession;
    }

    /**
     * Аутентификация пользователя
     *
     * @param string $login
     * @param string $passwd
     * @throws Core_Exception
     */
    public function auth($login, $passwd)
    {
        //Проверка наличия пользователя с указанным логином
        $userInfo = new App_Model_UserInfo();
        $where = $userInfo->select()->where('login = ?', $login);
        if (!$userInfo->fetchRow($where)->getId()) {
            throw new Core_Exception('Authentication failed', 5002);
        }

        //Проверка пароля
        $md5Passwd = $userInfo->initHashPassword($passwd);
        if ($md5Passwd != $userInfo->getPasswd()) {
            throw new Core_Exception('Authentication failed', 5002);
        }

        //Установка объекта данных пользователя
        $this->setUserInfo($userInfo);

        //Получение объекта пользователя
        $user = new App_Model_User();
        $where = $user->select()->where('id_service = ?', $this->getService()->getId())
                                ->where('id_service_user = ?', $userInfo->getId());
        if (!$user->fetchRow($where)->getId()) {
            throw new Core_Exception('Failed to find user record', 5003);
        }
        //Установка объекта модели пользователя
        $this->setUser($user);

        //Создание сессии
        $this->createSession($this->getUser()->getId());

        //Удаление предыдущих сессий пользователя
        $this->getUserSession()->delete('id_user = ' . $this->getUserSession()->getIdUser()
                                        . ' AND sid <> "' . $this->getUserSession()->getSid() . '"');
    }

    /**
     * Создание объекта сессии пользователя
     *
     * @param int $idUser Идентификатор пользователя
     * @throws Core_Exception
     */
    public function createSession($idUser)
    {
        //Создание сессии
        $session = new App_Model_UserSession();
        $session->setIdUser($idUser);
        $session->setStartTime(date('U'));
        if (!$session->save()) {
            throw new Core_Exception('Failed to create user session', 5004);
        }
        //Установка сессии пользователя
        $this->setUserSession($session);
    }

    /**
     * Поиск сессии по идентификатору
     *
     * @param string $sid Идентификатор сессии
     * @throws Core_Exception
     */
    public function findSession($sid)
    {
        //Поиск данных сессии
        $session = new App_Model_UserSession();
        $session->find($sid);
        if (!$session->getSid()) {
            throw new Core_Exception('User session was not found', 103);
        }
        //Установка объекта сессии
        $this->setUserSession($session);
    }

}
