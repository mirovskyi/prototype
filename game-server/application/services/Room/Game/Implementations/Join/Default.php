<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 01.03.12
 * Time: 12:34
 *
 * Дефолтная реализация добавления игрока за игровой стол
 */
class App_Service_Room_Game_Implementations_Join_Default
    extends  App_Service_Room_Game_Templates_Join
{

    /**
     * Баланс пользователя
     *
     * @var int
     */
    protected $_balance;

    /**
     * Проверка возможности добавления пользователя в игру
     *
     * @return bool
     * @throws Core_Exception
     */
    public function canJoin()
    {
        //Проверка наличия свободного места
        if ($this->_isMaxPlayersCount()) {
            //Игровой стол занят
            return false;
        }
        //Проверка соответсвия баланса пользователя
        if (!$this->_checkUserBalance()) {
            return false;
        }
        //Проверка мимнимального опыта игрока
        if (!$this->_checkExperience()) {
            return false;
        }

        //Пользователь соответствует требованиям настроек игрового стола
        return true;
    }

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
        $player = $this->getGameSession()->getData()->addOpponent($userSid, $name, $position);
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


    /**
     * Добавление пользователя в чат игры
     *
     * @param int $userChatType Тип пользователя (игрок|наблюдатель)
     * @throws Core_Exception
     */
    public function addUserInChat($userChatType = Core_Game_Chat_Player::REAL_PLAYER)
    {
        //Получение данных пользователя соц. сети
        $userInfo = $this->getUserSession()->getSocialUser();

        //Создание объекта данных пользователя в чате игры
        $chatPlayer = new Core_Game_Chat_Player();
        $chatPlayer->setSid($this->getUserSession()->getSid())
                   ->setType($userChatType)
                   ->setName($userInfo->getName());

        //Получаем объект модели сессии чата
        $chatSession = $this->getChatSession();
        //Добавляем пользователя в чат
        $chatSession->getChat()->addPlayer($chatPlayer);
    }

    /**
     * Проверка достижения максимального кол-ва игроков в игре
     *
     * @return bool
     */
    protected function _isMaxPlayersCount()
    {
        //Получаем количество игроков за игровым столом
        $playersCount = count($this->getGameSession()->getData()->getPlayersContainer());
        //Проверка наличия свободного места
        if ($this->getGameSession()->getData()->getMaxPlayersCount() > $playersCount) {
            //Игровой стол еще не заполнен
            return false;
        }

        //Игровой стол занят
        return true;
    }

    /**
     * Проверка соответствия баланса пользователя и минимального баланса для игрового стола
     *
     * @return bool
     * @throws Core_Exception
     */
    protected function _checkUserBalance()
    {
        //Получаем текущий баланс пользователя
        $balance = $this->_getUserBalance();

        //Проверка баланса
        if (false === $balance) {
            throw new Core_Exception('Failed to get user\'s account balance', 700);
        }
        if ($balance < $this->getGameSession()->getData()->getStartBet()) {
            throw new Core_Exception(
                'User\'s account balance should be more than the minimum balance specified in the settings of the game',
                306,
                Core_Exception::USER
            );
        }

        return true;
    }

    /**
     * Проверка соответствия опыта пользователя настройкам игрового стола
     *
     * @return bool
     */
    protected function _checkExperience()
    {
        //TODO: проверка соответствия опыта игрока с настройками игрового стола
        return true;
    }

    /**
     * Получение баланса пользователя
     *
     * @return int|bool
     */
    protected function _getUserBalance()
    {
        //Проверка наличия баланса пользователя в текущем экземпляре класса
        if (null !== $this->_balance) {
            return $this->_balance;
        }

        //Сервис балансов
        $balanceApi = new Core_Api_DataService_Balance();
        //Запрос получения баланса пользователя
        $this->_balance = $balanceApi->getUserBalance(
            $this->getUserSession()->getSocialUser()->getId(),
            $this->getUserSession()->getSocialUser()->getNetwork()
        );

        //Возвращаем полученную сумму баланса пользователя
        return $this->_balance;
    }

}

