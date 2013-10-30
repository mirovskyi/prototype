<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.03.12
 * Time: 18:03
 *
 * Обработчик пинга игрового стола "Дурак"
 */
class App_Service_Handler_Durak_Ping extends App_Service_Handler_Abstract
{

    /**
     * Получение данных игры
     *
     * @return Core_Game_Durak
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
            //Обработка таймаута игрока
            $this->_handleTimeout($active);
            //Возвращаем обновленные данные
            return $this->_getResponse(true);
        }

        //Проверка таймаута розыгрыша
        $process = $this->getGame()->getProcess();
        if ($process && $process->isTimerEnable() && $process->getTimeout(false) < 0) {
            $this->_handleProcessTimeout();
            return $this->_getResponse(true);
        }

        //Возвращаем ответ на пинг
        return $this->_getResponse();
    }

    /**
     * Обработка таймаутов игроков
     *
     * @param Core_Game_Durak_Players_Player $player
     * @return bool
     * @throws Exception
     */
    protected function _handleTimeout(Core_Game_Durak_Players_Player $player)
    {
        //Блокировка данных сессии игры и получение актуальных данных
        $this->getGameSession()->lockAndUpdate();
        //Проверка актуальности данных
        if ($this->getRequest()->get('command') != $this->getGame()->getCommand()) {
            //Разблокировка данных сессии
            $this->getGameSession()->unlock();
            return false;
        }

        //Обработка таймаутов
        try {
            //Инкремент порядкового номера обновления
            $this->getGame()->incCommand();
            //Обработка таймаута
            $this->getGame()->getProcess()->handleTimeout($player);
            //Обновление времено изменения
            $this->getGame()->setLastUpdate();
        } catch (Exception $e) {
            //Разблокировка данных сессии
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохраняем и разблокируем данные сессии
        $this->getGameSession()->saveAndUnlock();
        return true;
    }

    /**
     * Обработка таймаута розыгрыша
     *
     * @return bool
     * @throws Exception
     */
    public function _handleProcessTimeout()
    {
        //Блокировка данных сессии игры и получение актуальных данных
        $this->getGameSession()->lockAndUpdate();
        //Проверка актуальности данных
        if ($this->getRequest()->get('command') != $this->getGame()->getCommand()) {
            //Разблокировка данных сессии
            $this->getGameSession()->unlock();
            return false;
        }

        //Обработка таймаута
        try {
            //Инкремент порядкового номера обновления
            $this->getGame()->incCommand();
            //Завершение розыгрыша
            $this->getGame()->getProcess()->finish();
            //Очищаем данные розыгрыша
            $this->getGame()->clearProcess();
            //Обновление времено изменения
            $this->getGame()->setLastUpdate();
        } catch (Exception $e) {
            //Разблокировка данных сессии
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохраняем и разблокируем данные сессии
        $this->getGameSession()->saveAndUnlock();
        return true;
    }

    /**
     * Формирование ответа сервера
     *
     * @param bool $update
     * @return string
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
