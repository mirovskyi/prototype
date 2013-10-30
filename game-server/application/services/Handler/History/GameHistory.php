<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.05.12
 * Time: 16:33
 *
 * Обработчик получения подробной истории игры
 */
class App_Service_Handler_History_GameHistory extends App_Service_Handler_Abstract
{


    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @throws Core_Exception
     * @return string
     */
    public function handle()
    {
        //Получаем данные пользователя соц. сети
        $service = $this->getRequest()->get('service');
        $socialUser = new Core_Social_User($service, $this->getRequest()->get('vars'));

        //Объект работы с историей игр
        $history = Core_Game_History::getInstance();
        //Проверка наличия у пользователя купленной услуги истории игр
        if (!$history->isAllowHistory($socialUser->getId(), $service)) {
            throw new Core_Exception('The service of game history is not available', 3050, Core_Exception::USER);
        }

        //Получение идентификатора сессии игры
        $gameSid = $this->getRequest()->get('game');
        //Получение типа истории
        $historyType = $this->getRequest()->get('type');
        //Если история по дате, получаем указанную дату
        $date = $this->getRequest()->get('date');

        //Получение имени таблицы истории
        if ($historyType == 'favorite') {
            $tableName = $history->getDb()->getFavoriteTableName();
        } else {
            $tableName = $history->getDb()->getTableNameByDate($date);
        }

        //Формирование данных поиска игры в истории
        $record = new Core_Game_History_Db_Record();
        $record->setIdGame($gameSid);
        $record->setIdUser($socialUser->getId());
        $record->setNetwork($service);
        //Поиск данных в истории игр
        $result = $history->getDb()->get($record, $tableName);
        if (!$result) {
            throw new Core_Exception('');
        }

        //Возвращаем ответ сервера
        return $this->_getResponse($result);
    }

    /**
     * Получение ответа сервера
     *
     * @param Core_Game_History_Db_Record $record
     * @return string
     */
    private function _getResponse(Core_Game_History_Db_Record $record)
    {
        //Передача данных в шаблон вида
        $this->view->assign('history', $record);
        //Возвращаем ответ сервера
        return $this->view->render();
    }
}
