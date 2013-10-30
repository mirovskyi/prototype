<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 14.06.12
 * Time: 12:47
 *
 * Класс модели работы с опытом пользователей
 */
class App_Service_Server_Experience
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
     * Наименование игры
     *
     * @var string
     */
    protected $_gameName;

    /**
     * Объект модели пользователя
     *
     * @var App_Model_User
     */
    protected $_user;

    /**
     * Объект модели игры
     *
     * @var App_Model_Game
     */
    protected $_game;

    /**
     * Объект модели опыта пользователя
     *
     * @var App_Model_UserExperience
     */
    protected $_experience;


    /**
     * Создание нового объекта
     *
     * @param string $idServiceUser Идентификатр пользователя в соц. сети
     * @param string $nameService   Имя сервиса соц. сети
     * @param        $game          Наименование игры
     */
    public function __construct($idServiceUser, $nameService, $game)
    {
        $this->_idServiceUser = $idServiceUser;
        $this->_nameService = $nameService;
        $this->_gameName = $game;
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
     * Установка объекта модели игры
     *
     * @param App_Model_Game $game
     *
     * @return App_Service_Server_Experience
     */
    public function setGame(App_Model_Game $game)
    {
        $this->_game = $game;
        return $this;
    }

    /**
     * Получение объекта модели игры
     *
     * @throws Zend_Exception
     * @return App_Model_Game
     */
    public function getGame()
    {
        if (null === $this->_game) {
            //Создание объекта модели игры
            $game = new App_Model_Game();
            $game->fetchRow($game->select()->where('name = ?', $this->_gameName));
            //Проверка наличия данных игры
            if (!$game->getId()) {
                throw new Zend_Exception('Game ' . $this->_gameName . ' not found in database');
            }
            //Установка модели
            $this->setGame($game);
        }
        return $this->_game;
    }

    /**
     * Установка объекта модели опыта пользователя
     *
     * @param App_Model_UserExperience $userExperience
     *
     * @return App_Service_Server_Experience
     */
    public function setUserExperience(App_Model_UserExperience $userExperience)
    {
        $this->_experience = $userExperience;
        return $this;
    }

    /**
     * Получение объекта модели опыта пользователя
     *
     * @return App_Model_UserExperience
     */
    public function getUserExperience()
    {
        if (null === $this->_experience) {
            //Создание объекта модели
            $userExperience = new App_Model_UserExperience();
            //Поиск данных в базе
            $where = $userExperience->select()->where('id_user = ?', $this->getUser()->getId())
                                              ->where('id_game = ?', $this->getGame()->getId());
            $userExperience->fetchRow($where);
            //Установка объекта модели
            $this->setUserExperience($userExperience);
        }
        return $this->_experience;
    }

    /**
     * Инкремент количества сыгранных партий
     *
     * @return bool|int
     */
    public function increment()
    {
        //Обновление данных опыта
        $this->getUserExperience()->setIdUser($this->getUser()->getId());
        $this->getUserExperience()->setIdGame($this->getGame()->getId());
        $this->getUserExperience()->setNumber($this->getUserExperience()->getNumber() + 1);
        return $this->getUserExperience()->save();
    }

    /**
     * Инкремент количества выиграшей пользователя
     *
     * @return bool|int
     */
    public function win()
    {
        //Обновление количества выиграшей
        $this->getUserExperience()->setIdUser($this->getUser()->getId());
        $this->getUserExperience()->setIdGame($this->getGame()->getId());
        $this->getUserExperience()->setWin($this->getUserExperience()->getWin() + 1);
        return $this->getUserExperience()->save();
    }


}
