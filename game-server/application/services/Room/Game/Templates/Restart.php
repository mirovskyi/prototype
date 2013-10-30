<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 09.05.12
 * Time: 12:02
 *
 * Шаблонный метод рестарта игры
 */
abstract class App_Service_Room_Game_Templates_Restart
{

    /**
     * Объект модели сессии игрока
     *
     * @var App_Model_Session_User
     */
    protected $_userSession;

    /**
     * Объект модели сессии игры
     *
     * @var App_Model_Session_Game
     */
    protected $_gameSession;


    /**
     * __construct
     *
     * @param App_Model_Session_Game|null $game
     * @param App_Model_Session_User|null $user
     */
    public function __construct(App_Model_Session_Game $game = null, App_Model_Session_User $user = null)
    {
        if (null !== $game) {
            $this->setGameSession($game);
        }
        if (null !== $user) {
            $this->setUserSession($user);
        }
    }

    /**
     * Установка объекта модели сессии игрока
     *
     * @param App_Model_Session_User $userSession
     * @return App_Service_Room_Game_Templates_Join
     */
    public function setUserSession(App_Model_Session_User $userSession)
    {
        $this->_userSession = $userSession;
        return $this;
    }

    /**
     * Получение объекта модели сессии игрока
     *
     * @return App_Model_Session_User
     * @throws Core_Exception
     */
    public function getUserSession()
    {
        return $this->_userSession;
    }

    /**
     * Установка объекта модели сессии игры
     *
     * @param App_Model_Session_Game $game
     * @return App_Service_Room_Game_Templates_DeleteUser
     */
    public function setGameSession(App_Model_Session_Game $game)
    {
        $this->_gameSession = $game;
        return $this;
    }

    /**
     * Получение объета модели сессии игры
     *
     * @return App_Model_Session_Game
     * @throws Core_Exception
     */
    public function getGameSession()
    {
        return $this->_gameSession;
    }

    /**
     * Реализация рестарта игры
     *
     * @abstract
     * @throws Core_Exception
     * @return void
     */
    public function restart()
    {
        //Данные игрового стола
        $game = $this->getGameSession()->getData();
        //Проверка текущего статуса игры
        if ($game->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            throw new Core_Exception('Game not finished', 213, Core_Exception::USER);
        }

        //Проверка закрытия игрового стола (когда из-за стола встает создатель)
        $closeGameEvent = App_Service_Events_Gameclose::name();
        if ($game->hasEvent($closeGameEvent)) {
            throw new Core_Exception('Game session has closed', 308, Core_Exception::USER);
        }

        //Проверка наличия у пользователя достаточной суммы на балансе
        if ($game->getStartBet() > $this->_getUserBalance()) {
            throw new Core_Exception(
                'Failed to restart game. User balance should be more than game bet',
                305,
                Core_Exception::USER
            );
        }

        //Сбрасываем сумму ставки до начального значения
        $game->setBet($game->getStartBet());
        //Удаление всех наблюдателей состояния игры
        $game->detachAll();
        //Обновление наблюдателей состояния объекта игры
        $this->reattachOvservers();

        //Инициализация игры
        $this->reinitialize();
    }

    /**
     * Прикрепение наблюдателей игры
     *
     * @abstract
     * @return void
     */
    abstract public function reattachOvservers();

    /**
     * Инициализация игры
     *
     * @abstract
     * @return void
     */
    abstract public function reinitialize();

    /**
     * Получение суммы остатка на блансе пользователя
     *
     * @return bool|int
     */
    protected function _getUserBalance()
    {
        //API доступа к данным баланса
        $api = new Core_Api_DataService_Balance();
        //Получаем остаток на балансе пользователя
        return $api->getUserBalance(
            $this->getUserSession()->getSocialUser()->getId(),
            $this->getUserSession()->getSocialUser()->getNetwork()
        );
    }

}
