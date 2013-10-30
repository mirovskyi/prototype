<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 06.09.12
 * Time: 15:32
 *
 * Помошник вида. Формирование блока статуса игры
 */
class App_Service_View_Helper_Status extends Core_View_Helper_Abstract
{

    /**
     * Выполнение действий помошника вида
     *
     * @param Core_Game_Abstract $game   Объект данных игры
     * @param string            $userSid Идентификатор сессии игрока
     *
     * @return string
     */
    public function status(Core_Game_Abstract $game, $userSid)
    {
        //Получение текущего статуса игры
        $status = $game->getViewStatus($userSid);
        //Формирование блока статуса игры
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startElement('status');
        //Определение флага возможности приглашения оппоненров
        $invite = 0;
        if ($status == Core_Game_Abstract::STATUS_WAIT && $this->_getGameSession()) {
            //Проверка приватности стола
            $isPrivate = $this->_getGameSession()->getMode() == App_Model_Session_Game::PRIVATE_MODE;
            //Проверяем, является ли теккущий игрок создателем стола
            $isCreator = $this->_getGameSession()->getCreatorSid() == $userSid;
            //Проверяем возможность приглашения оппонентов
            if ($isPrivate && $isCreator) {
                $invite = 1;
            }
        }
        //Установка флага возможности приглашения оппонентов
        $xml->writeAttribute('invite', $invite);
        //Установка значения статуса
        $xml->text($status);
        //Закрываем элемент
        $xml->endElement();

        //Возвращаем данные статуса игры
        return $xml->flush(false);
    }

    /**
     * Получение объекта сессии игры
     *
     * @return App_Model_Session_Game
     */
    private function _getGameSession()
    {
        return Core_Session::getInstance()->get(Core_Session::GAME_NAMESPACE);
    }

}
