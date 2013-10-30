<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.06.12
 * Time: 11:44
 *
 * Обработчик запроса пинга игры Домино
 */
class App_Service_Handler_Domino_Ping extends App_Service_Handler_Abstract
{

    /**
     * Получение данных игры
     *
     * @return Core_Game_Domino
     */
    public function getGame()
    {
        return $this->getGameSession()->getData();
    }

    /**
     * Инициализация обработчика
     *
     * @throws Exception
     * @return void
     */
    public function init()
    {
        //Уничтожение обработанных событий
        $this->_destroyHandledEvents();
        //Проверка старта новой партии
        $this->_checkForRestartGame();
    }


    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    public function handle()
    {
        //Проверка актуальности данных игры
        if ($this->getGame()->getCommand() != $this->getRequest()->get('command')) {
            //Возвращаем ответ с актуальными данными игры
            return $this->_getResponse(true);
        }

        //Проверка статуса игры
        $status = $this->getGame()->getStatus();
        if ($status != Core_Game_Abstract::STATUS_PLAY) {
            //Возвращаем ответ на ping
            return $this->_getResponse();
        }

        //Проверка наличия таймаута активного игрока
        $active = $this->getGame()->getPlayersContainer()->getActivePlayer();
        if ($active && $active->getRestGametime($this->getGame()->getLastUpdate(), false) < 0) {
            //Обработка таймаута
            $this->_handleTimeout($active);
            //Возвращаем обновленные данные
            return $this->_getResponse(true);
        }

        //Возвращаем ответ на пинг
        return $this->_getResponse();
    }

    /**
     * Обработка достижения таймаута активным игроком
     *
     * @param Core_Game_Domino_Players_Player $loser
     *
     * @throws Exception
     * @return void
     */
    private function _handleTimeout(Core_Game_Domino_Players_Player $loser)
    {
        //Блокировка и обновление данных сессии игры
        $this->getGameSession()->lockAndUpdate();
        //Проверка актуальности данных
        if ($this->getGame()->getCommand() != $this->getRequest()->get('command')) {
            //Разблокируем данные
            $this->getGameSession()->unlock();
            return;
        }
        try {
            //Окончание игры, установка проигравшего
            $this->getGame()->finish($loser);
            //Обновлние состояния игры
            $this->getGame()->updateGameState();
        } catch (Exception $e) {
            //Разблокировка данных сессии игры
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }
        //Сохраняем и разблокируем данные сессии игры
        $this->getGameSession()->saveAndUnlock();
    }

    /**
     * Формирование ответа сервера
     *
     * @param bool $update
     * @return string
     */
    private function _getResponse($update = false)
    {
        //Данные чата
        $chat = App_Model_Session_GameChat::chat($this->getGameSession()->getSid())->saveXml(
            $this->getUserSession()->getSid(),
            $this->getRequest()->get('chatId', 0),
            $update
        );

        //Передаем данные игры в шаблон вида
        $this->view->assign(array(
            'game' => $this->getGame(),
            'chat' => $chat
        ));

        //Если флаг установлен, меняем шаблон и передаем данные игроков
        if ($update) {
            //Меняем шаблон
            $this->view->setTemplate($this->getGame()->getName() . '/update');
            //Передача данных игрового стола в шаблон
            $this->_assignViewGameData(false);
        }

        //Формирование ответа
        return $this->view->render();
    }
}
