<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 17.02.12
 * Time: 17:05
 *
 * Класс описывающий методы получения общих данных игрового сервиса
 */
class App_Server_Info
{

    /**
     * Объект модели
     *
     * @var App_Service_Server_Info
     */
    protected $_model;


    /**
     * __construct
     */
    public function __construct()
    {
        $this->_model = new App_Service_Server_Info();
    }

    /**
     * Метод получения списка игр
     *
     * @return App_Model_Game
     */
    public function getGames()
    {
        return $this->getModel()->getGames();
    }

    /**
     * Получение данных пользователя
     *
     * @param string $idServiceUser
     * @param string $nameService
     * @return array
     */
    public function getUserInfo($idServiceUser, $nameService)
    {
        return $this->getModel()->getUserInfoArray($idServiceUser, $nameService);
    }

    /**
     * Установка флага пользователю
     *
     * @param string $idServiceUser Идентификатор пользовтаеля в соц. сети
     * @param string $nameService Наименование соц. сети
     * @param $idFlag Порядковый номер флага (всего 5)
     * @param bool $flag Значение флага
     * @return bool
     * @throws Zend_Exception
     */
    public function setUserFlag($idServiceUser, $nameService, $idFlag, $flag = true)
    {
        return $this->getModel()->setUserFlag($idServiceUser, $nameService, $idFlag, $flag);
    }

    /**
     * Получение объекта модели
     *
     * @return App_Service_Server_Info
     */
    protected function getModel()
    {
        return $this->_model;
    }

}
