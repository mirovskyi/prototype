<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.09.12
 * Time: 18:21
 * To change this template use File | Settings | File Templates.
 */
class Core_Api_DataService_Bonus extends Core_Api_DataService_Abstract
{

    /**
     * Установка настроек
     *
     * @param array $options
     * @return Core_Api_DataService_Abstract
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (is_array($this->getOption('bonus'))) {
            foreach($this->getOption('bonus') as $name => $value) {
                $this->setOption($name, $value);
            }
        }
    }

    /**
     * Зачисление ежечасного бонуса
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     *
     * @return int Текущий баланс пользователя
     */
    public function addHourBonus($idServiceUser, $nameService)
    {
        return $this->_getCLient()->addHourBonus($idServiceUser, $nameService);
    }

    /**
     * Зачисление ежедневного бонуса
     *
     * @param string $idServiceUser Идентификатор пользователя в соц. сети
     * @param string $nameService   Наименование соц. сети
     *
     * @return int Текущий баланс пользователя
     */
    public function addDailyBonus($idServiceUser, $nameService)
    {
        return $this->_getCLient()->addDailyBonus($idServiceUser, $nameService);
    }

}
