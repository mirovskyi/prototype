<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 21.03.12
 * Time: 11:20
 *
 * Декоратор ответа сервера. Обарачивание ответа в тег <methodCall>
 */
class App_Service_View_Decorator_MethodCall extends Core_View_Decorator_Abstract
{

    /**
     * Получение контента шаблона
     *
     * @throws Core_View_Exception
     * @param string|null $template
     * @return string
     */
    protected function _render($template = null)
    {
        //Определяем имя вызываемого метода
        $request = Core_Server::getInstance()->getServer()->getRequest();
        $methodCall = $request->getHandlerName() . '.' . $request->getMethod();

        //Создание тега methodCall
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startElement('methodCall');
        $xmlWriter->writeAttribute('name', $methodCall);

        //Проверка наличия сессии игры
        $session = $this->_getGameSession();
        if ($session) {
            //Добавляем аттрибут ID команды обновления данных игры
            $xmlWriter->writeAttribute('command', (string)$session->getData()->getCommand());
            //Получаем объект данных чата
            $chat = App_Model_Session_GameChat::chat($session->getSid());
            //Добавляем аттрибут ID чата
            $xmlWriter->writeAttribute('chat', (string)$chat->getCurrentId());
        } elseif ($request->get('game')) {
            //Добавляем данные чата игрового зала
            $game = $request->get('game');
            $chat = new App_Model_Room_Chat($game);
            //Добавляем аттрибут ID чата
            $xmlWriter->writeAttribute('chat', (string)$chat->getChat()->getCurrentId());
        }

        $xmlWriter->endElement();
        $xml = $xmlWriter->flush(false);

        //Возвращаем ответ с тэгом methodCall
        return $xml . parent::_render($template);
    }

    /**
     * Получение объекта сессии игры
     *
     * @return App_Model_Session_Game|bool
     */
    private function _getGameSession()
    {
        return Core_Session::getInstance()->get(Core_Session::GAME_NAMESPACE);
    }

}
