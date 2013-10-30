<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 16.04.12
 * Time: 11:55
 *
 * Обработка запроса покинуть карту в розыгрыш
 */
class App_Service_Handler_DurakTransfer_Throw extends App_Service_Handler_Abstract
{

    /**
     * Получение объекта игры
     *
     * @return Core_Game_DurakTransfer
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
        //Проверка актуальности данные игровой сессии
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
     * Обработка запроса
     *
     * @return void
     * @throws Core_Exception
     */
    protected function _handle()
    {
        //Инкремент порядкового номера обновления
        $this->getGame()->incCommand();

        //Обновление остатка времени на партию у атакующего игрока
        $this->_updateGameTimeout();

        //Идентификатор сессии игрока подкидывающего карты
        $opponentSid = $this->getUserSession()->getSid();
        //Карты
        $cards = $this->getRequest()->get('cards');
        //Флаг показа карты при переводе
        $showCardForTransfer = $this->getRequest()->get('showcard', false);
        //Подбрасываем карты
        $this->getGame()->throwCards($opponentSid, $cards, $showCardForTransfer);

        //Проверка завершения розыгрыша
        if ($this->getGame()->getProcess()->isEndProcess()) {
            //Завершение розыгрыша
            $endGame = $this->getGame()->getProcess()->finish();
            //Очищаем данные розыгрыша
            $this->getGame()->clearProcess();
            //Обработка завершения партии
            if ($endGame) {
                $this->getGame()->finishGame();
            }
        }

        //Обновление времено изменения
        $this->getGame()->setLastUpdate();
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
     * Обновление остатка времени партии текущего игрока
     * ВЫЗЫВАТЬ ДО ИЗМЕНЕНИЯ ДАННЫХ РОЗЫГРЫША (Core_Game_DurakTransfer_Process)
     */
    private function _updateGameTimeout()
    {
        //Проверка первого хода в розыгрыше
        if (count($this->getGame()->getProcess())) {
            //Это не первый ход атакующего игрока (для подбрасывания карт таймер не идет)
            return;
        }

        //Получаем данные текущего игрока
        $player = $this->getGame()->getPlayersContainer()->getPlayer($this->getUserSession()->getSid());
        //Получение остатка времени на  партию
        $gameTimeount = $this->getGame()->getPlayerGametime($player);
        //Обновление остатка времени на партию игрока
        $player->setGametime($gameTimeount);
    }

}
