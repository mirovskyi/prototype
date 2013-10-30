<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 09.04.12
 * Time: 12:02
 *
 * Наблюдатель закрытия игрового стола
 */
class App_Service_Observers_GameDestroy implements SplObserver
{

    /**
     * Обработка изменения состояния игры
     *
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject)
    {
        $this->_handle($subject);
    }

    /**
     * Обработка закрытия игрового стола
     *
     * @param Core_Game_Abstract $game
     * @return void
     */
    protected function _handle(Core_Game_Abstract $game)
    {
        //Проверка соответствия данной игры и игры из данных сессии
        $session = $this->_getGameSession($game->getId());
        if (!$session) {
            return;
        }

        //Проверка статуса игры
        if ($game->getStatus() != Core_Game_Abstract::STATUS_FINISH) {
            return;
        }

        //Проверка наличия события закрытия игрового стола
        if (!$game->hasEvent(App_Service_Events_Gameclose::name())) {
            return;
        }

        //Удаление игры из игрового зала
        $this->_deleteGameFromRoom($session);

        //Сессию в мусор)
        $this->_pushInTrash($session);

        //Удаляем наблюдателя
        $game->detach($this);
    }

    /**
     * Удаление данных игрового стола из игрового зала
     *
     * @param App_Model_Session_Game $session
     */
    private function _deleteGameFromRoom(App_Model_Session_Game $session)
    {
        //Имя игры
        $gameName = $session->getName();
        //Создание объекта модели данных игрового зала
        $room = new App_Model_Room($gameName, false);

        //Флаг блокировки данных игрового зала в данном контексте
        $isLock = false;
        //Проверка блокировки данных игрового зала текущим процессом
        if (!$room->isLock(posix_getpid())) {
            //Данные не заблокированы либо заблокированны другим процессом
            //Пытаемся захватить ключ блокировки
            $room->lock();
            //Переключаем флаг блокировки данных игрового зала
            $isLock = true;
        }
        //Получаем актуальные данные игрового зала
        $room->find($room->getNamespace());

        //Удаление данных игрового стола из игрового зала
        $room->delGame($session->getSid());

        //Сохранение данных игрового зала
        $room->save();
        //Если необходимо, разблокируем данные игрового зала
        if ($isLock) {
            $room->unlock();
        }
    }

    /**
     * Добавление сессии игры в "мусорку"
     *
     * @param App_Model_Session_Game $session
     */
    private function _pushInTrash(App_Model_Session_Game $session)
    {
        //Объект модели "мусорки"
        $trash = new App_Model_Trash(App_Model_Trash::GAME_NAMESPACE);
        //Блокируем и получаем актуальные данные "мусорки"
        $trash->lockAndUpdate();
        //Добавляем идентификатор сессии игры в мусорку
        $trash->addItem($session->getSid());
        //Сохраняем и разблокируем данные "мусорки"
        $trash->saveAndUnlock();
    }

    /**
     * Получение объекта данны сессии игры
     *
     * @param string $sid Идентификатор сессии игры
     *
     * @return App_Model_Session_Game|bool
     */
    private function _getGameSession($sid)
    {
        $session = Core_Session::getInstance()->get(Core_Session::GAME_NAMESPACE);
        if ($session != $sid) {
            $session = new App_Model_Session_Game();
            if (!$session->find($sid)) {
                return false;
            }
        }

        return $session;
    }
}
