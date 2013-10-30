<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 22.02.12
 * Time: 19:03
 *
 * Логика создания "быстрой" игры
 */
class App_Service_Handler_Room_Open_QuickGame
{

    /**
     * Объект данных игрового зала
     *
     * @var App_Model_Room
     */
    protected $_room;

    /**
     * Объект модели данных сессии игрока
     *
     * @var App_Model_Session_User
     */
    protected $_userSession;

    /**
     * Наименование игры
     *
     * @var string
     */
    protected $_gameName;

    /**
     * Баланс пользователя
     *
     * @var int
     */
    protected $_balance;

    /**
     * Опыт пользователя
     *
     * @var int
     */
    protected $_experience;


    public function __construct(App_Model_Room $room, App_Model_Session_User $session, $gameName)
    {
        //Установка данных игрового зала
        $this->setRoom($room);
        //Установка сессии пользователя
        $this->setUserSession($session);
        //Установка наименования игры
        $this->setGameName($gameName);
        //Установка текущего баланса пользователя
        $api = new Core_Api_DataService_Balance();
        $this->setUserBalance($api->getUserBalance(
            $session->getSocialUser()->getId(),
            $session->getSocialUser()->getNetwork()
        ));
        //Установка текущего опыта пользователя в игре
        $api = new Core_Api_DataService_Experience();
        $this->setUserExperience($api->getUserExperience(
            $session->getSocialUser()->getId(),
            $session->getSocialUser()->getNetwork(),
            $gameName
        ));
    }

    /**
     * Установка объекта данных игрового зала
     *
     * @param App_Model_Room $room
     */
    public function setRoom($room)
    {
        $this->_room = $room;
    }

    /**
     * Получение объекта данных игрового зала
     *
     * @return App_Model_Room
     */
    public function getRoom()
    {
        return $this->_room;
    }

    /**
     * Установка объекта модели данных сессии пользователя
     *
     * @param App_Model_Session_User $userSession
     */
    public function setUserSession($userSession)
    {
        $this->_userSession = $userSession;
    }

    /**
     * Получение объекта модели данных сессии пользователя
     *
     * @return App_Model_Session_User
     */
    public function getUserSession()
    {
        return $this->_userSession;
    }

    /**
     * Установка наименования игры
     *
     * @param string $gameName
     */
    public function setGameName($gameName)
    {
        $this->_gameName = $gameName;
    }

    /**
     * Получение наименования игры
     *
     * @return string
     */
    public function getGameName()
    {
        return $this->_gameName;
    }

    /**
     * Установка текущего баланса пользователя
     *
     * @param int $balance
     */
    public function setUserBalance($balance)
    {
        $this->_balance = $balance;
    }

    /**
     * Получение текущего баланса пользователя
     *
     * @return int
     */
    public function getUserBalance()
    {
        return $this->_balance;
    }

    /**
     * Установка опыта пользователя
     *
     * @param int $experience
     */
    public function setUserExperience($experience)
    {
        $this->_experience = $experience;
    }

    /**
     * Получение опыта игрока
     *
     * @return int
     */
    public function getUserExperience()
    {
        return $this->_experience;
    }


    /**
     * Создание "быстрой" игры
     *
     * @throws Exception
     * @return App_Model_Session_Game
     */
    public function createQuickGame()
    {
        //Получаем актуальные данные игрового зала, блокируем данные игрового зала
        $this->getRoom()->lockAndUpdate();

        //Попытка добавления игрока за существующий стол либо создание нового
        try {
            //Получение/создание игрового стола
            $game = $this->_getQuickGame();
        } catch (Exception $e) {
            //Разблокируем данные игрового зала
            $this->getRoom()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Установка флага игры пользователя за игровым столом
        $this->getRoom()->setUserInGame($this->getUserSession()->getSid(), $game->getSid());

        //Добавление игрового стола в игровой зал
        $this->getRoom()->addGame($game->getSid());

        //Сохраняем данные игрового зала и разблокируем его
        $this->getRoom()->saveAndUnlock();

        //Возвращаем данные сессии игры
        return $game;
    }

    /**
     * Создание/получение игрового стола за который был добавлен пользователь
     *
     * @return App_Model_Session_Game
     * @throws Core_Exception
     */
    protected function _getQuickGame()
    {
        //Проверка наличия пользователя в игровом зале
        if (!$this->getRoom()->hasUser($this->getUserSession()->getSid())) {
            throw new Core_Exception('User session was not found in room', 301);
        }

        //Поиск подходящей открытой игры для пользователя
        $game = $this->_findGameForUser();
        //Проверка наличия подходящей игры
        if (false !== $game) {
            //Заисываем найденую сессию игры в реестр
            Core_Session::getInstance()->set(Core_Session::GAME_NAMESPACE, $game);
            //Добавляем пользователя за игровой стол
            $gameJoin = App_Service_Room_Game_Join::factory($this->getGameName(), $game, $this->getUserSession());
            $gameJoin->join();
            //Возвращаем данные сессии игры
            return $game;
        }

        //Создание нового игрового стола с параметрами по умолчанию (без параметров)
        $gameCreate = App_Service_Room_Game_Create::factory($this->getGameName(), $this->getUserSession());
        //Возвращаем созданную игровую сессию
        return $gameCreate->create();
    }

    /**
     * Поиск соответствующей игры для пользователя
     *
     * @return App_Model_Session_Game|bool
     */
    protected function _findGameForUser()
    {
        //Перебираем все игры в зале
        foreach($this->getRoom()->getGames() as $gameSid) {
            //Получаем данные игрового стола
            $game = new App_Model_Session_Game();
            if (!$game->find($gameSid)) {
                continue;
            }
            //Проверка режима игрового стола (только публичный стол)
            if ($game->getMode() == App_Model_Session_Game::PRIVATE_MODE) {
                continue;
            }
            //Проверяем возможность сесть за игровой стол
            $playersCount = count($game->getData()->getPlayersContainer());
            $maxPlayersCount = $game->getData()->getMaxPlayersCount();
            if ($maxPlayersCount <= $playersCount) {
                continue;
            }
            //Проверка состояния игрового стола
            if ($game->getData()->getStatus() != Core_Game_Abstract::STATUS_WAIT) {
                continue;
            }
            //Проверка состояния игроков, все игроки должны быть в игре
            $isPlay = true;
            foreach($game->getData()->getPlayersContainer() as $player) {
                //Проверка состояния игрока в игре
                if (!$player->isPlay()) {
                    $isPlay = false;
                    break;
                }
                //Проверка состояния сессии пользователя, проверка на "зомби"
                $user = new App_Model_Session_User();
                if (!$user->find($player) || $user->isZombie()) {
                    $isPlay = false;
                    break;
                }
            }
            if (!$isPlay) {
                continue;
            }
            //Проверка соответствия баланса игрока текущей ставке
            if ($this->getUserBalance() < $game->getMinBalance()) {
                continue;
            }
            //Проверка соответствия опыта пользователя
            if ($this->getUserExperience() < $game->getMinExperience()) {
                continue;
            }
            //Пользователь может сесть за стол
            return $game;
        }

        return false;
    }
}
