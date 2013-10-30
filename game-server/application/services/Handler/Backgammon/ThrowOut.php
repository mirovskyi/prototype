<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 27.06.12
 * Time: 15:52
 *
 * Обработчик запроса вывода шашки в игре "Нарды"
 */
class App_Service_Handler_Backgammon_ThrowOut extends App_Service_Handler_Abstract
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
     * Обработка запроса. Возвращает ответ сервера
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
            //Вывод шашки
            $this->_handleThrowOut();
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
     * Вывод шашки за пределы игровой доски
     *
     * @return void
     */
    protected function _handleThrowOut()
    {
        //Инкремент номера команды обновления данных игры
        $this->getGame()->incCommand();
        //Получение параметров запроса
        $position = $this->getRequest()->get('from');
        //Вывод шашки
        $this->getGame()->throwOut($position);
        //Проверка возможности хода
        $activePlayer = $this->getGame()->getPlayersContainer()->getActivePlayer();
        if ($this->getGame()->getBoard()->canMove($activePlayer)) {
            //У игрока еще есть возможность хода
            return;
        }
        //Проверка окончания игры
        if ($this->getGame()->isFinish()) {
            //Добавление очка за победу в партии активному игроку
            $activePlayer->addPoints(1);
            //Завершение партии
            $this->getGame()->finishGame();
        } else {
            //Обновление времени на партию активного игрока
            $activePlayer->setGametime($this->getGame()->getPlayerGametime($activePlayer));
            //Переход хода к следующему игроку
            $this->getGame()->getPlayersContainer()->switchActivePlayer();
            //Выбрасывание костей
            $this->getGame()->throwDice();
        }
        //Обновление времени именения данных игры
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
}
