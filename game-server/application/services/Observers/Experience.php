<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 14.06.12
 * Time: 12:15
 *
 * Класс наблюдателя изменений состояния игры и обновления опыта игроков
 */
class App_Service_Observers_Experience implements SplObserver
{

    /**
     * Обработка обновления наблюдаемого объекта
     *
     * @param SplSubject $subject
     * @return void
     */
    public function update(SplSubject $subject)
    {
        $this->_handle($subject);
    }

    /**
     * Обработа обновления данных игры
     *
     * @param Core_Game_Abstract $game
     */
    private function _handle(Core_Game_Abstract $game)
    {
        //Обработка окончания игры
        if ($game->getStatus() == Core_Game_Abstract::STATUS_FINISH) {
            //API сервера данных (опыт играков)
            $api = new Core_Api_DataService_Experience();
            //Обновление данных опыта игроков
            foreach($game->getPlayersContainer() as $player) {
                //Получение сессии пользователя
                $userSession = new App_Model_Session_User();
                if ($userSession->find($player)) {
                    //Обновление данных опыта
                    $api->increment(
                        $userSession->getSocialUser()->getId(),
                        $userSession->getSocialUser()->getNetwork(),
                        $game->getName()
                    );
                    //Проверка победы пользователя
                    if ($player->getStatus() == Core_Game_Players_Player::STATUS_WINNER) {
                        //Увеличиваем количество побед пользователя
                        $api->win(
                            $userSession->getSocialUser()->getId(),
                            $userSession->getNetwork(),
                            $game->getName()
                        );
                    }
                }
            }

            //Удалние слушателя
            $game->detach($this);
        }
    }
}
