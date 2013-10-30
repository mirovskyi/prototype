<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 14.06.12
 * Time: 15:20
 * Класс (одиночка) клиента API сервера балансов
 */
class Core_Api_DataService_Experience extends Core_Api_DataService_Abstract
{

    /**
     * Установка настроек
     *
     * @param array $options
     * @return Core_Api_DataService_Abstract|Core_Api_DataService_Experience
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (is_array($this->getOption('experience'))) {
            foreach($this->getOption('experience') as $name => $value) {
                $this->setOption($name, $value);
            }
        }
        return $this;
    }

    /**
     * Метод получения опыта пользователя
     *
     * @param string $idServiceUser Идентификатор пользователя в системе соц. сети
     * @param string $nameService   Имя сервиса социальной сети
     * @param        $game          Наименование игры
     *
     * @return int
     */
    public function getUserExperience($idServiceUser, $nameService, $game)
    {
        try {
            return $this->_getCLient()->getExperience($idServiceUser, $nameService, $game);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API experience error:' . PHP_EOL . $ex);
            }
            return false;
        }
    }

    /**
     * Получение количества побед пользователя
     *
     * @param string $idServiceUser Идентификатор пользователя в системе соц. сети
     * @param string $nameService   Имя сервиса социальной сети
     * @param string $game          Наименование игры
     *
     * @return int
     */
    public function getWinCount($idServiceUser, $nameService, $game)
    {
        try {
            return $this->_getCLient()->getWinCount($idServiceUser, $nameService, $game);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API experience error:' . PHP_EOL . $ex);
            }
            return false;
        }
    }

    /**
     * Инкремент количества сыгранных партий игроком
     *
     * @param string $idServiceUser Идентификатор пользователя в системе соц. сети
     * @param string $nameService   Имя сервиса социальной сети
     * @param string $game          аименование игры
     *
     * @return bool
     */
    public function increment($idServiceUser, $nameService, $game)
    {
        try {
            return $this->_getCLient()->increment($idServiceUser, $nameService, $game);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API increment experience error:' . PHP_EOL . $ex);
            }
            return false;
        }
    }

    /**
     * Инкремент количество выиграшей пользователя
     *
     * @param string $idServiceUser Идентификатор пользователя в системе соц. сети
     * @param string $nameService   Имя сервиса социальной сети
     * @param string $game          аименование игры
     *
     * @return bool
     */
    public function win($idServiceUser, $nameService, $game)
    {
        try {
            return $this->_getCLient()->win($idServiceUser, $nameService, $game);
        } catch (Exception $ex) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('API win experience error:' . PHP_EOL . $ex);
            }
            return false;
        }
    }

}

