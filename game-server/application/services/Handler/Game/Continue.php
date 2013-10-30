<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 07.04.12
 * Time: 18:42
 *
 * Обработчик запроса начать игру заново
 */
class App_Service_Handler_Game_Continue extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
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
        //Объект игры
        $game = $this->getGameSession()->getData();

        //Проверка текущего статуса игры
        if ($game->getStatus() == Core_Game_Abstract::STATUS_PLAY) {
            throw new Core_Exception('Game not finished');
        }
        if ($game->getStatus() == Core_Game_Abstract::STATUS_FINISH) {
            //Генерируем начальные данные игры
            $game->generate();
        }

        //Изменение статуса игрока
        $player = $game->getPlayersContainer()->getPlayer($this->getUserSession()->getSid());
        $player->setPlay();

        //Проверка возможности начать игру
        if ($game->canPlay()) {
            //Начало игры
            //TODO: установка активного игрока
            $game->getPlayersContainer()->setActive($this->getGameSession()->getCreatorSid());
            $game->setStatus(Core_Game_Abstract::STATUS_PLAY);
        } else {
            //Ожидание оппонентов
            $game->setStatus(Core_Game_Abstract::STATUS_WAIT);
        }
    }

    /**
     * Получение ответа сервера
     *
     * @return string
     */
    protected function _getResponse()
    {
        //Получение данных игроков
        $userInfo = App_Model_Session_User::getUsersDataFromPlayersContainer(
            $this->getGameSession()->getData()->getPlayersContainer()
        );

        //Данные чата
        $chat = App_Model_Session_GameChat::chat($this->getGameSession()->getSid())->saveXml(
            $this->getUserSession()->getSid(),
            $this->getRequest()->get('chatId', 0)
        );

        //Формирование данных ответа
        $this->view->assign(array(
            'userSess' => $this->getUserSession()->getSid(),
            'gameSess' => $this->getGameSession()->getSid(),
            'game' => $this->getGameSession()->getData(),
            'userInfo' => $userInfo,
            'chat' => $chat
        ));

        //Отдаем данные шаблона игры
        $template = $this->getGameSession()->getData()->getName() . '/update';
        return $this->view->render($template);
    }
}
