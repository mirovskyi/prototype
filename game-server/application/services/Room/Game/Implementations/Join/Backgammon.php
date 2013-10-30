<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.06.12
 * Time: 11:17
 *
 * Реализация алгоритма добавления пользователя в игру Домино
 */
class App_Service_Room_Game_Implementations_Join_Backgammon
    extends App_Service_Room_Game_Implementations_Join_Default
{

    /**
     * Метод добавления пользователя в игру
     *
     * @abstract
     * @param int|null $position Позиция места игрового стола, за которое садится игрок
     * @throws Core_Exception
     */
    public function joinPlayer($position = null)
    {
        //Инкремент порядкового номера обновления
        $this->getGameSession()->getData()->incCommand();

        //Получаем идентификатор сессии пользователя
        $userSid = $this->getUserSession()->getSid();
        //Имя пользователя
        $name = $this->getUserSession()->getSocialUser()->getName();
        //Добавление игрока за игровой стол
        $player = $this->getGameSession()->getData()->addOpponent($userSid, $name, null, null, $position);
        //Установка текущего баланса добавленного игрока
        $player->setBalance($this->_getUserBalance());

        //Проверка возможности начала игры
        if ($this->getGameSession()->getData()->canPlay()) {
            //Генерация начального состояния игрового стола
            $this->getGameSession()->getData()->generate();
            //Старт игры
            $this->getGameSession()->getData()->setStatus(Core_Game_Abstract::STATUS_PLAY);
        } else {
            //Меняем статус на ожидание (WAIT) для случая, когда текущий статус FINISH
            $this->getGameSession()->getData()->setStatus(Core_Game_Abstract::STATUS_WAIT);
        }

        //Обновление времено изменения
        $this->getGameSession()->getData()->setLastUpdate();
    }

}
