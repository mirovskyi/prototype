<?php
 
class App_Service_Handler_Room_Users extends App_Service_Handler_Abstract
{

    /**
     * Получение имени игры
     *
     * @return string
     */
    public function getGameName()
    {
        return $this->getRequest()->get('game');
    }

    /**
     * Обработка запроса
     *
     * @throws Core_Protocol_Exception
     * @return string
     */
    public function handle()
    {
        //Объект игрового зала
        $room = new App_Model_Room($this->getGameName());

        //Получение списка пользователей в игровом зале
        $roomService = new App_Service_Room($room);
        $users = $roomService->getUsers();
        //Удаляем из списка текущего пользователя
        if (isset($users[$this->getUserSession()->getSid()])) {
            unset($users[$this->getUserSession()->getSid()]);
        }

        //Проверка соответствия пользователей параметрам приватного стола
        //Если получны данные приватного стола
        if (count($this->getRequest()->get('gamedata', array()))) {
            //Получение списка пользователей подходящих под условия приватного стола
            $users = $this->_gameDataFilter($users, $this->getRequest()->get('gamedata'));
        } elseif ($this->hasGameSession()) {
            //Получение списка пользователей подходящих под условия приватного стола
            $users = $this->_gameDataFilter($users);
        } else {
            throw new Core_Protocol_Exception('Not all required parameters are received', 638, Core_Exception::USER);
        }

        //Передаем в шаблон ответа список игроков
        $this->view->assign('users', $users);

        //Возвращаем полученный ответ
        return $this->view->render();
    }

    /**
     * Получение списка пользователей, которые подходят под параметры игрового стола
     *
     * @param App_Model_Session_User[] $users    Список сессий поьзователей
     * @param array|null               $gameData Данные игрового стола
     *
     * @return App_Model_Session_User[] Отфильтрованный список пользователей. Только пользователи, которым доступен игровой стол
     */
    private function _gameDataFilter($users, $gameData = null)
    {
        //Проверка наличия пользователей
        if (!count($users)) {
            return $users;
        }

        //Формирование параметров игры
        if (null === $gameData) {
            $gameData = array(
                'bet' => $this->getGameSession()->getData()->getStartBet(),
                'mb' => $this->getGameSession()->getMinBalance(),
                'me' => $this->getGameSession()->getMinExperience()
            );
        }

        //Проверка соответствия каждого пользователя
        $allowUsers = array();
        foreach($users as $userSession) {
            //Получение баланса пользователя
            $api = new Core_Api_DataService_Balance();
            $balance = $api->getUserBalance(
                $userSession->getSocialUser()->getId(),
                $userSession->getSocialUser()->getNetwork()
            );
            //Проверка баланса пользователя
            if (isset($gameData['bet']) && $balance < $gameData['bet']) {
                continue;
            }
            //Проверка соответствия минимальному балансу за игровым столом
            if (isset($gameData['mb']) && $balance < $gameData['mb']) {
                continue;
            }
            //Проверка опыта игрока
            if (isset($gameData['me']) && $gameData['me'] > 0) {
                //Получение опыта пользователя
                $api = new Core_Api_DataService_Experience();
                $experience = $api->getUserExperience(
                    $userSession->getSocialUser()->getId(),
                    $userSession->getSocialUser()->getNetwork(),
                    $this->getGameName()
                );
                //Проверка соответствия опыта пользователя
                if ($experience < $gameData['me']) {
                    continue;
                }
            }
            //Добавление пользователя в список
            $allowUsers[] = $userSession;
        }

        //Возвращаем писок допустимых сессий пользователей
        return $allowUsers;
    }

}
