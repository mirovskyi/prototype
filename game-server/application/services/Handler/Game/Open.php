<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 07.04.12
 * Time: 9:25
 *
 * Обработка запроса входа в игру как наблюдателя
 */
class App_Service_Handler_Game_Open extends App_Service_Handler_Abstract
{

    /**
     * Получение имени игры
     *
     * @return string
     */
    public function getGameName()
    {
        return $this->getRequest()->get('game');
    }

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @return string
     */
    public function handle()
    {
        //Добаляем пользователя в игру как наблюдателя
        $join = App_Service_Room_Game_Join::factory(
            $this->getGameName(),
            $this->getGameSession(),
            $this->getUserSession(),
            true
        );
        $join->join();

        //Возвращаем ответ сервера
        return $this->_getResponse();
    }

    /**
     * Получение данных ответа
     *
     * @return string
     */
    protected function _getResponse()
    {
        //Передача данных игрового стола в шаблон
        $this->_assignViewGameData();

        //Флаг открытия игрового стола
        $this->view->assign('isOpen', true);

        //Отдаем данные шаблона игры
        $template = $this->getGameSession()->getData()->getName() . '/update';
        return $this->view->render($template);
    }
}
