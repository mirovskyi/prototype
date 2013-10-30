<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 14.03.12
 * Time: 14:54
 *
 * Обработка запроса добавления сообщения в чат
 */
class App_Service_Handler_Chat_Message extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса.
     *
     * @return string
     */
    public function handle()
    {
        //Объект модели сессии чата
        $chatSess = new App_Model_Session_GameChat();
        $chatSess->setSid($this->getGameSession()->getSid());
        //Создание ключа блокировки данных сессии чата
        $chatSess->lock();
        //Получаем актуальные данные сессии чата
        if (!$chatSess->find($chatSess->getSid())) {
            $chatSess->unlock();
            throw new Core_Exception('Chat session was not found', 105);
        }

        //Добавление сообщения
        try {
            $this->_addMessage($chatSess->getChat());
        } catch (Exception $e) {
            //Разблокируем данные сессии
            $chatSess->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохраняем данные сессии чата
        $chatSess->save();
        //Разблокируем данные сессии чата
        $chatSess->unlock();

        //Передача данных чата в шаблон ответа
        $this->view->assign('chat', $chatSess->getChat()->saveXml(
            $this->getUserSession()->getSid(),
            $this->getRequest()->get('chatId'),
            false
        ));
        //Возвращаем ответ сервера
        return $this->view->render();
    }

    /**
     * Добавление сообщения в чат
     *
     * @param Core_Game_Chat $chat
     */
    protected function _addMessage(Core_Game_Chat $chat)
    {
        //Текст сообщения
        $text = $this->getRequest()->get('text');
        //Идентификатор сессии отправителя
        $sender = $this->getUserSession()->getSid();
        //Адресат сообщения
        if (null != $this->getRequest()->get('recipient')) {
            $recipient = $this->getRequest()->get('recipient');
        } else {
            $recipient = Core_Game_Chat_Message::ALL_PLAYERS;
        }

        //Добавляем сообщение
        $chat->addMessage($text, $sender, $recipient);
    }




}
