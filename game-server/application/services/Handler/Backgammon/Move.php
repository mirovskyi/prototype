<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 27.06.12
 * Time: 15:52
 *
 * Обработчик перемещения шашки на игровой доске Нард
 */
class App_Service_Handler_Backgammon_Move extends App_Service_Handler_Abstract
{

    /**
     * Получение объекта игры
     *
     * @return Core_Game_Backgammon
     */
    public function getGame()
    {
        return $this->getGameSession()->getData();
    }

    /**
     * Обработка запроса перемещения шашки. Возвращает ответ сервера
     *
     * @throws Exception
     * @return string
     */
    public function handle()
    {
        //Получаем актуальные данные и блокируем сессию игры
        $this->getGameSession()->lockAndUpdate();
        //Проверка актуальности запроса
        if ($this->getRequest()->get('command') != $this->getGame()->getCommand()) {
            //Разблокируем данные игровой сессии
            $this->getGameSession()->unlock();
            //Возвращаем актуальные данные игры
            return $this->_getResponse();
        }

        //Обработка запроса перемещения шашки
        try {
            //Перемещение шашки
            $this->_handleMove();
        } catch (Exception $e) {
            //Разблокируем данные сессии игры
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }
        //Сохраняем и разблокируем данные сессии игры
        $this->getGameSession()->saveAndUnlock();
        //Возвращаем данные игры
        return $this->_getResponse();
    }

    /**
     * Обработка перемещения шашки
     *
     * @return void
     */
    protected function _handleMove()
    {
        //Инкремент номера команды обновления данных игры
        $this->getGame()->incCommand();
        //Получаем данные перемещения
        $fromPosition = $this->getRequest()->get('from');
        $toPosition = $this->getRequest()->get('to');
        //Перемещение шашки
        $this->getGame()->move($fromPosition, $toPosition);
        //Если у пользователя больше нет возможности перемещения шашки, передаем право хода оппоненту
        $activePlayer = $this->getGame()->getPlayersContainer()->getActivePlayer();
        if (!$this->getGame()->getBoard()->canMove($activePlayer)) {
            //Обновление времени на партию активного игрока
            $activePlayer->setGametime($this->getGame()->getPlayerGametime($activePlayer));
            //Переключение активного игрока
            $this->getGame()->getPlayersContainer()->switchActivePlayer();
            //Генерим (выбрасываем) новые значения игральных костей
            $this->getGame()->throwDice();
            //Обновление времени именения данных игры
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
}
