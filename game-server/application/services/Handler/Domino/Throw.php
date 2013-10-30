<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.06.12
 * Time: 12:00
 *
 * Обработчик запроса добавления игралной кости в ряд
 */
class App_Service_Handler_Domino_Throw extends App_Service_Handler_Abstract
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
     * Обработка запроса. Возвращает ответ сервера
     *
     *
     * @throws Core_Exception
     * @throws Exception
     * @return string
     */
    public function handle()
    {
        //Проверка активности игрока
        $active = $this->getGame()->getPlayersContainer()->getActivePlayer();
        if ($active->getSid() != $this->getUserSession()->getSid()) {
            throw new Core_Exception('Wrong order of data update', 205, Core_Exception::USER);
        }
        //Обновление и блокировка данных сессии игры
        $this->getGameSession()->lockAndUpdate();
        //Проверка актуальности данных игры
        if ($this->getGame()->getCommand() != $this->getRequest()->get('command')) {
            //Разблокируем данные сессии игры
            $this->getGameSession()->unlock();
            //Возвращаем ответ с актуальными данными игры
            return $this->_getResponse();
        }
        //Попытка добавить игральную кость в ряд
        try {
            //Добавление игральной кости в ряд
            $this->_throwBone();
        } catch (Exception $e) {
            //Разблокировка игровой сессии
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }
        //Сохранение и разблокировка игровой сессии
        $this->getGameSession()->saveAndUnlock();

        //Возвращаем ответ сервера
        return $this->_getResponse();
    }

    /**
     * Добавление игральной кости в ряд на игровом столе
     *
     * @throws Core_Exception
     * @return void
     */
    private function _throwBone()
    {
        //Получение текущего игрока
        $player = $this->getGame()->getPlayersContainer()->getPlayer($this->getUserSession()->getSid());
        //Данные обновления из запроса
        $bone = $this->getRequest()->get('bone');
        $side = strtoupper($this->getRequest()->get('side'));
        //Определение стороны ряда для добавления кости
        if ($side == 'L') {
            $placement = Core_Game_Domino_Bone_Array::PREPEND;
        } elseif ($side == 'R') {
            $placement = Core_Game_Domino_Bone_Array::APPEND;
        } else {
            throw new Core_Exception('Invalid placement', 3023, Core_Exception::USER);
        }
        //Инкремент порядкового номера обновления данных игры
        $this->getGame()->incCommand();
        //Добавление игральной кости
        $this->getGame()->throwBone($player, $bone, $placement);
        //Обновление таймаута партии игрока
        $player->setGametime($this->getGame()->getPlayerGametime($player));
        //Переключение активного игрока
        $this->getGame()->switchActivePlayer();
        //Проверка окончания розыгрыша
        if ($this->getGame()->isFinish()) {
            //Завершение розыгрыша
            $this->getGame()->finish();
        }
        //Обновляем время последних изменений игры
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
