<?php

/**
 * Description of Room
 *
 * @author aleksey
 */
class App_Service_Room
{

    /**
     * Объект модели игрового зала
     *
     * @var App_Model_Room
     */
    protected $_room;


    /**
     * __construct
     *
     * @param App_Model_Room|null $room
     */
    public function __construct(App_Model_Room $room = null)
    {
        if (null !== $room) {
            $this->setRoom($room);
        }
    }

    /**
     * Установка объекта модели игрового зала
     *
     * @param App_Model_Room $room
     * @return App_Service_Room
     */
    public function setRoom(App_Model_Room $room)
    {
        $this->_room = $room;
        return $this;
    }

    /**
     * Получение объекта модели игрового зала
     *
     * @return App_Model_Room
     */
    public function getRoom()
    {
        return $this->_room;
    }

    /**
     * Получение списка данных сессий игроков в игровом зале (не за игровым столом)
     *
     * @return App_Model_Session_User[]
     */
    public function getUsers()
    {
        $result = array();
        foreach($this->getRoom()->getUsersSid() as $userSid) {
            //Если игрок сидит за игровым столом, пропускаем его
            if ($this->getRoom()->isUserInGame($userSid)) {
                continue;
            }
            //Получаем объект данных сессии игрока
            $session = new App_Model_Session_User();
            if ($session->find($userSid)) {
                $result[$userSid] = $session;
            }
        }
        return $result;
    }

    /**
     * Получение списка данных сессий игровых столов
     *
     * @return App_Model_Session_Game[]
     */
    public function getGames()
    {
        $result = array();
        foreach($this->getRoom()->getGames() as $gameSid) {
            $session = new App_Model_Session_Game();
            if ($session->find($gameSid)) {
                $result[] = $session;
            }
        }
        return $result;
    }

    /**
     * Получение данных игр в игровом зале
     *
     * @param App_Model_Session_User|null $userSess Объект сессии пользователя для которого формируется список игровых столов
     *
     * @return array
     */
    public function getGamesInfo(App_Model_Session_User $userSess = null)
    {
        //Получаем данные текущего пользователя
        if ($userSess) {
            //Текущий баланс пользователя
            $api = new Core_Api_DataService_Balance();
            $balance = $api->getUserBalance(
                $userSess->getSocialUser()->getId(),
                $userSess->getSocialUser()->getNetwork()
            );
            //Текущий опыт игрока
            $api = new Core_Api_DataService_Experience();
            $experience = $api->getUserExperience(
                $userSess->getSocialUser()->getId(),
                $userSess->getSocialUser()->getNetwork(),
                $this->getRoom()->getNamespace()
            );
        }

        //Формирование списка игровых столов
        $result = array();
        foreach($this->getGames() as $game) {
            //Проверка возможности сесть за игровой стол у текущего пользователя
            $enable = 1;
            if ($userSess) {
                //Проверка приватности стола
                if ($game->getMode() == App_Model_Session_Game::PRIVATE_MODE) {
                    $enable = 0;
                }
                //Проверка баланса
                if ($enable && $balance < $game->getMinBalance()) {
                    $enable = 0;
                }
                //Проверка соответствия опыта игрока
                if ($enable && $experience < $game->getMinExperience()) {
                    $enable = 0;
                }
            }
            //Установка данных игры
            $entity = array(
                'sid' => $game->getSid(),
                'enable' => $enable,
                'vip' => intval($game->isVip()),
                'timestep' => $game->getData()->getRunTimeout(),
                'timegame' => $game->getData()->getGameTimeout(),
                'bet' => $game->getData()->getBet(),
                'minBalance' => $game->getMinBalance(),
                'minExperience' => $game->getMinExperience(),
                'spectator' => $game->getSpectator(),
                'status' => $game->getData()->getStatus(),
                'users' => array()
            );
            //Получаем данные игроков за игровым столом
            foreach($game->getData()->getPlaces() as $pos => $player) {
                $user = new App_Model_Session_User();
                if ($player && $user->find($player->getSid())) {
                    //Для игроков "зомби" в начале имени ставим три точки
                    $prefix = $user->isZombie() ? '(...)' : '';
                    //Установка данных игрока
                    $entity['users'][$pos] = array(
                        'sid' => $user->getSid(),
                        'id' => $user->getSocialUser()->getId(),
                        'name' => $prefix . $user->getSocialUser()->getName(),
                        'photo' => $user->getSocialUser()->getPhotoUrl()
                    );
                } else {
                    $entity['users'][$pos] = null;
                }
            }
            //Добавляем данные игры
            $result[] = $entity;
        }
        return $result;
    }
}