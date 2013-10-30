<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 17.02.12
 * Time: 16:57
 *
 * Класс получение общих данных игрового сервиса
 */
class Core_Api_DataService_Info extends Core_Api_DataService_Abstract
{

    /**
     * Получение списка игр
     *
     * @return array
     */
    public function getGames()
    {
        return $this->_getCLient()->getGames();
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
        return $this->_getCLient()->getUserInfo($idServiceUser, $nameService);
    }

    /**
     * Установка флага пользователю
     *
     * @param string $idServiceUser Идентификатор пользовтаеля в соц. сети
     * @param string $nameService Наименование соц. сети
     * @param $idFlag Порядковый номер флага (всего 5)
     * @param bool $flag Значение флага
     * @return bool
     */
    public function setUserFlag($idServiceUser, $nameService, $idFlag, $flag = true)
    {
        return $this->_getCLient()->setUserFlag($idServiceUser, $nameService, $idFlag, $flag);
    }

}
