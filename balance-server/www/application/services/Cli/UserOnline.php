<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 21.05.12
 * Time: 11:01
 *
 * Модель обновления количества игроков онлайн
 */
class App_Service_Cli_UserOnline
{

    /**
     * Обновление количества игроков в онлайн
     */
    public function updateOnline()
    {
        //Обновление данных online каждые 2 секунды
        while(true) {
            //Получение списка игровых серверов
            $games = new App_Model_Game();
            foreach($games->fetchAll() as $game) {
                $online = $this->_getOnline($game->getUrl(), $game->getName());
                if (false !== $online) {
                    //Обновление данных игры в БД
                    $game->setOnline($online)
                         ->save();
                    //Логирование обновлений
                    //$this->_log('UPDATE ONLINE FOR GAME `' . $game->getName() . '` - ' . $online);
                }
            }
            //Задержка в 2 секунды
            sleep(2);
        }
    }

    /**
     * Получение количество игроков в онлайне
     *
     * @param string $url
     * @param string $gameName
     * @return string
     * @throws Exception
     */
    private function _getOnline($url, $gameName)
    {
        //Попытка получение игроков в онлайн от игрового сервера
        try {
            $client = new Core_Game_Client($url);
            $response  = $client->online(array('game' => $gameName));
            //Получение количества игроков онлайн
            $online = $response->xpath('//online');
            if (!is_array($online) || !isset($online[0])) {
                throw new Exception('Unknown getOnline response: '
                                    . PHP_EOL . $client->__lastResponse());
            }
            //Возвращаем количество игроков online
            return (string)$online[0];
        } catch (Exception $e) {
            $this->_log($e);
            return false;
        }
    }

    /**
     * Логирование
     *
     * @param string $message
     */
    private function _log($message)
    {
        if (Zend_Registry::getInstance()->isRegistered('log')) {
            Zend_Registry::get('log')->info($message);
        }
    }

}
