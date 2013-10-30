<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 23.04.12
 * Time: 16:10
 * To change this template use File | Settings | File Templates.
 */
class App_Service_Handler_Durak_Refuse extends App_Service_Handler_Abstract
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
        //Инкремент порядкового номера обновления данных игры
        $this->getGame()->incCommand();

        //Получение объекта игрока
        $sid = $this->getUserSession()->getSid();
        $player = $this->getGame()->getPlayersContainer()->getPlayer($sid);
        //Отказ подкинуть карты
        $this->getGame()->throwRefuse($player);

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
    public function _getResponse()
    {
        //Передача данных игрового стола в шаблон
        $this->_assignViewGameData();
        //Формирование ответа
        $this->view->setTemplate($this->getGame()->getName() . '/update');
        return $this->view->render();
    }

}
