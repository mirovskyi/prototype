<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 07.04.12
 * Time: 17:42
 *
 * Обработка запроса сдаться в игре
 */
class App_Service_Handler_Event_Surrender extends App_Service_Handler_Abstract
{


    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @throws Exception
     * @return string
     */
    public function handle()
    {
        //Блокируем данные игры
        $this->getGameSession()->lockAndUpdate();
        //Обработка запроса сдаться
        try {
            $this->_handle();
        } catch (Exception $e) {
            //Разблокируем данные игры
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Созраняем и разблокируем данные игры
        $this->getGameSession()->saveAndUnlock();

        //Возвращаем ответ
        return $this->_getResponse();
    }

    /**
     * Обработка запроса здаться
     *
     * @throws Core_Exception
     */
    protected function _handle()
    {
        //В зависимости от игры получаем реализацию обработки события
        switch ($this->getGameSession()->getData()->getName()) {
            case Core_Game_Durak::GAME_NAME: {
                $surrender = new App_Service_Handler_Event_Surrender_Durak();
            }
                break;
            case Core_Game_Domino::GAME_NAME: {
                $surrender = new App_Service_Handler_Event_Surrender_Domino();
            }
                break;
            default: {
                $surrender = new App_Service_Handler_Event_Surrender_Default();
            }
                break;
        }

        //Обработка события
        $surrender->surrender($this->getUserSession(), $this->getGameSession());
    }

    /**
     * Получение ответа сервера
     *
     * @return string
     */
    protected function _getResponse()
    {
        //Объект игры
        $game = $this->getGameSession()->getData();

        //Получение данных игроков
        $userInfo = App_Model_Session_User::getUsersDataFromPlayersContainer(
            $game->getPlayersContainer()
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
            'game' => $game,
            'userInfo' => $userInfo,
            'chat' => $chat
        ));

        //Отдаем данные шаблона игры
        $template = $game->getName() . '/update';
        return $this->view->render($template);
    }

}
