<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 27.06.12
 * Time: 15:52
 *
 * Обработчик пинга игры "Нарды"
 */
class App_Service_Handler_Backgammon_Ping extends App_Service_Handler_Abstract
{

    /**
     * Получение объекта игры "Нарды"
     *
     * @return Core_Game_Backgammon
     */
    public function getGame()
    {
        return $this->getGameSession()->getData();
    }

    /**
     * Инициализация обработчика
     *
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
     * Обработка комманды PING
     *
     * @return string
     */
    public function handle()
    {
        //Проверка соответствия идентификатора комманды обновления данных игры
        if ($this->getRequest()->get('command') != $this->getGame()->getCommand()) {
            //Возвращаем данные игры
            return $this->_getResponse(true);
        }

        //Проверка истечения времени партии у активного игрока
        if ($this->getGame()->getActivePlayerGametime(false) < 0) {
            //Получение актуальных данных игровой сесиии, блокировка сессии
            $this->getGameSession()->lockAndUpdate();
            //Проверка актуальности данных игры
            if ($this->getRequest()->get('command') != $this->getGame()->getCommand()) {
                //Возвращаем данные игры
                return $this->_getResponse(true);
            }
            //Инкремент команды обновления данных
            $this->getGame()->incCommand();
            //Обработка истечения вемени партии
            try {
                $this->_handleTimeout();
            } catch (Exception $e) {
                //Разблокировка данных сессии
                $this->getGameSession()->unlock();
                //Выбрасываем исключение
            }
            //Сохранение времени последнего обновления
            $this->getGame()->setLastUpdate();
            //Сохранение и разблокировка данных сессии
            $this->getGameSession()->saveAndUnlock();
            //Возвращаем обновленные данные игры
            return $this->_getResponse(true);
        }

        //Возвращаем ответ на PING
        return $this->_getResponse();
    }

    /**
     * Обработка истечения времени у активного игрока
     *
     * @return void
     */
    protected function _handleTimeout()
    {
        //Объект текущего активного игрока
        $activePlayer = $this->getGame()->getPlayersContainer()->getActivePlayer();
        //Получаем оппонента игрока, у которого закончилось время
        $iterator = $this->getGame()->getPlayersContainer()->getIterator();
        $iterator->setCurrentElement($activePlayer);
        $winner = $iterator->nextElement();
        //Проверяем были ли ходы у игрока, которого закончилось время
        if ($this->getGame()->getBoard()->hasMovement($activePlayer->getId())) {
            //Игрок совершал хода, проигрышь партии
            //Добавление очка за выигрышь оппоненту
            $winner->addPoints(1);
            //Завершение партии
            $this->getGame()->finishGame();
        } else {
            //Установка победителя
            $this->getGame()->setWinner($winner);
            //Меняем статус игры
            $this->getGame()->setStatus(Core_Game_Abstract::STATUS_FINISH);
        }
    }

    /**
     * Получение ответа сервера
     *
     * @param bool $update Флаг передачи актуальных данных игры
     * @return mixed|string
     */
    protected function _getResponse($update = false)
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
