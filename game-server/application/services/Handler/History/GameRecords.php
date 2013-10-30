<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.05.12
 * Time: 15:48
 *
 * Обработчик получения записей из истории игры
 */
class App_Service_Handler_History_GameRecords extends App_Service_Handler_Abstract
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

        //Проверка наличия у пользователя купленной услуги истории игр
        if (!Core_Game_History::getInstance()->isAllowHistory($socialUser->getId(), $service)) {
            throw new Core_Exception('The service of game history is not available', 3050, Core_Exception::USER);
        }

        //Наименование игры
        $gameName = $this->getRequest()->get('game');
        //Получение типа истории
        $historyType = $this->getRequest()->get('type');
        //Если история по дате, получаем указанную дату
        $date = $this->getRequest()->get('date');

        //Получение списка записей игр в истории
        $records = $this->_getHistoryRecords($socialUser->getId(), $service, $gameName, $historyType, $date);

        //Возвращаем ответ сервера
        return $this->_getResponse($records, $gameName, $historyType, $date);
    }

    /**
     * Получение записей в истории игры
     *
     * @param string $idUser Идентификатор пользователя в соц. сети
     * @param string $network Имя соц. сети
     * @param string $game Системное наименование игры
     * @param string $type Тип истории (избранные игры | игры за дату)
     * @param string|null $date Дата истории (игры за дату)
     * @return Core_Game_History_Db_Record[]
     */
    private function _getHistoryRecords($idUser, $network, $game, $type, $date = null)
    {
        //Объект работы с историей игр
        $history = Core_Game_History::getInstance();
        //Формирование имени таблицы истории
        if ($type == 'favorite') {
            $tableName = $history->getDb()->getFavoriteTableName();
        } else {
            $tableName = $history->getDb()->getTableNameByDate($date);
        }

        //Получение списка записей в истории игры
        return $history->getDb()->getGameRecords($tableName, $idUser, $network, $game);
    }

    /**
     * Получение ответа сервера
     *
     * @param Core_Game_History_Db_Record[] $records
     * @param string $game Наименование игры
     * @param string $type Тип истории
     * @param string|null $date Дата
     *
     * @return string
     */
    private function _getResponse($records, $game, $type, $date = null)
    {
        //Передача списка записей истории в шаблон ответа
        $this->view->assign(array(
            'records' => $records,
            'game' => $game,
            'type' => $type,
            'date' => $date
        ));
        //Возвращаем ответ сервера
        return $this->view->render();
    }
}
