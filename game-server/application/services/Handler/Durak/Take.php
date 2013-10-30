<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 16.04.12
 * Time: 13:00
 * To change this template use File | Settings | File Templates.
 */
class App_Service_Handler_Durak_Take extends App_Service_Handler_Abstract
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
     * Обработка запроса взять карты
     *
     * @throws Core_Exception
     */
    protected function _handle()
    {
        //Получаем объект отбивающегося пользователя
        $defender = $this->getGame()->getPlayersContainer()->getDefenderPlayer();
        //Проверка соответствия отбивающегося и игрока пославшего запрос
        if ($this->getUserSession()->getSid() != $defender->getSid()) {
            throw new Core_Exception('Player is not defender', 3012, Core_Exception::USER);
        }

        //Инкремент порядкового номера обновления
        $this->getGame()->incCommand();

        //Установка поражения игрока в текущем розыгрыше
        $this->getGame()->getProcess()->setLose();
        //Проверка окончания розыгрыша
        if ($this->getGame()->getProcess()->isEndProcess()) {
            //Завершение розыгрыша
            $endGame = $this->getGame()->getProcess()->finish();
            //Очистка данных розыгрыша
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
    public function _getResponse()
    {
        //Передача данных игрового стола в шаблон
        $this->_assignViewGameData();
        //Формирование ответа
        $this->view->setTemplate($this->getGame()->getName() . '/update');
        return $this->view->render();
    }

}
