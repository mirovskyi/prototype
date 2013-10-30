<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 16.04.12
 * Time: 12:00
 * To change this template use File | Settings | File Templates.
 */
class App_Service_Handler_Durak_Beatoff extends App_Service_Handler_Abstract
{

    /**
     * Получение объекта игры
     *
     * @return Core_Game_Durak
     */
    public function getGame()
    {
        return $this->getGameSession()->getData();
    }


    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @throws Exception
     * @return string
     */
    public function handle()
    {
        //Проверка актуальности данных игровой сессии
        $this->getGameSession()->lockAndUpdate();
        if ($this->getGame()->getCommand() != $this->getRequest()->get('command')) {
            //Разблокируем данные игровой сессии
            $this->getGameSession()->unlock();
            //Возвращаем актуальные данные игры
            return $this->_getResponse();
        }

        //Обработка запроса
        try {
            $this->_handle();
        } catch (Exception $e) {
            //Разблокируем данные игровой сессии
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохраняем данные игровой сессии
        $this->getGameSession()->saveAndUnlock();

        //Возвращаем обновленные данные игры
        return $this->_getResponse();
    }

    /**
     * Обработка запроса бить карту
     *
     * @throws Core_Exception
     */
    protected function _handle()
    {
        //Получаем битую и бьющую карты
        $cards = explode(':', $this->getRequest()->get('cards'));
        if (count($cards) < 2) {
            throw new Core_Exception('');
        }

        //Инкремент порядкового номера обновления данных игры
        $this->getGame()->incCommand();

        //Обновление остатка времени игрока на партию
        $this->_updateGameTimeout();

        //Бьем карту
        $this->getGame()->beatOffCard($cards[0], $cards[1]);

        //Проверяем, отбил ли игрок все карты
        if ($this->getGame()->getProcess()->isDefend()) {
            //Изменяем время последнего обновления данных игры
            $this->getGame()->setLastUpdate();
        }

        //Проверка окончания розыгрыша
        if ($this->getGame()->getProcess()->isEndProcess()) {
            //Завершение розыгрыша
            $endGame = $this->getGame()->getProcess()->finish();
            //Очищаем данные розыгрыша
            $this->getGame()->clearProcess();
            //Завершение партии
            if ($endGame) {
                $this->getGame()->finishGame();
            }
            //Изменяем время последнего обновления данных игры
            $this->getGame()->setLastUpdate();
        }
    }

    /**
     * Формирование ответа сервера
     *
     * @return string
     */
    protected function _getResponse()
    {
        //Передача данных игрового стола в шаблон
        $this->_assignViewGameData();
        //Формирование ответа
        $this->view->setTemplate($this->getGame()->getName() . '/update');
        return $this->view->render();
    }

    /**
     * Обновление остатка времени на партию игрока
     */
    private function _updateGameTimeout()
    {
        //Получение данных игрока
        $player = $this->getGame()->getPlayersContainer()->getPlayer($this->getUserSession()->getSid());
        //Обновление остатка времени на партию игрока
        $gameTimeout = $this->getGame()->getPlayerGametime($player);
        $player->setGametime($gameTimeout);
    }

}
