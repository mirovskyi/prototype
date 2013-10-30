<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 28.05.12
 * Time: 15:04
 *
 * Обработчик получения списка игр в истории пользоваиеля
 */
class App_Service_Handler_History_Games extends App_Service_Handler_Abstract
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

        //Получеаем объект работы с данными истории
        $history = Core_Game_History::getInstance();
        //Проверка наличия у пользователя купленной услуги истории игр
        if (!$history->isAllowHistory($socialUser->getId(), $service)) {
            throw new Core_Exception('The service of game history is not available', 3050, Core_Exception::USER);
        }

        //Получаем список игр за текущий день
        $current = $history->getDb()->getGames(
            $history->getDb()->getCurrentTableName(),
            $socialUser->getId(),
            $service
        );
        //Получение списка игр за предыдущий день
        $previous = $history->getDb()->getGames(
            $history->getDb()->getPreviousTableName(),
            $socialUser->getId(),
            $service
        );
        //Получение списка избранных игр
        $favorite = $history->getDb()->getGames(
            $history->getDb()->getFavoriteTableName(),
            $socialUser->getId(),
            $service
        );

        //Формирование ответа сервера
        return $this->_getResponse($current, $previous, $favorite);
    }

    /**
     * Получение ответа сервера
     *
     * @param array $currentDayGames
     * @param array $previousDayGames
     * @param array $favoriteGames
     * @return string
     */
    private function _getResponse($currentDayGames, $previousDayGames, $favoriteGames)
    {
        //Формирование списка сохраненных игр пользователя за все время
        $games = array_unique(array_merge(
            array_keys($currentDayGames),
            array_keys($previousDayGames),
            array_keys($favoriteGames)
        ));

        //Получение текущей даты и даты за предыдущий день
        $currentDate = date('Y-m-d');
        $previousDate = new DateTime();
        $previousDate->modify('-1 days');

        //Передача данных в шаблон ответа
        $this->view->assign(array(
            'games' => $games,
            'currentDate' => $currentDate,
            'currentDayGames' => $currentDayGames,
            'previousDate' => $previousDate->format('Y-m-d'),
            'previousDayGames' => $previousDayGames,
            'favoriteGames' => $favoriteGames
        ));
        //Возвращаем ответ сервера
        return $this->view->render();
    }
}
