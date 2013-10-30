<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 07.04.12
 * Time: 18:42
 *
 * Обработчик запроса начать игру заново
 */
class App_Service_Handler_Game_Restart extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @throws Exception
     * @return string
     */
    public function handle()
    {
        //Блокировка данных игры
        $this->getGameSession()->lockAndUpdate();

        //Обработка запроса
        try {
            $this->_handle();
        } catch (Exception $e) {
            //Разблокировка данных игры
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохранение и разблокировка данных игры
        $this->getGameSession()->saveAndUnlock();

        //Возвращаем ответ сервера
        return $this->_getResponse();
    }

    /**
     * Обработка запроса
     *
     * @throws Core_Exception
     */
    protected function _handle()
    {
        //Реализация рестарта игры
        $restart = App_Service_Room_Game_Restart::factory(
            $this->getGameSession()->getName(),
            $this->getGameSession(),
            $this->getUserSession()
        );
        //Рестарт игры
        $restart->restart();
    }

    /**
     * Получение ответа сервера
     *
     * @return string
     */
    protected function _getResponse()
    {
        //Передача данных игрового стола в шаблон
        $this->_assignViewGameData();

        //Отдаем данные шаблона игры
        $template = $this->getGameSession()->getData()->getName() . '/update';
        return $this->view->render($template);
    }
}
