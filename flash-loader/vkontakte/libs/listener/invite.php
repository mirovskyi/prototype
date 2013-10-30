<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 10.10.12
 * Time: 18:49
 *
 * Обработчик события перехода в приложение по приглощению друга
 */
class Invite implements HandlerInterface
{

    /**
     * Проверка наличия события для обработки
     *
     * @param array $params Параметры запроса
     * @return bool
     */
    public function hasEvent($params)
    {
        //Проверка наличия параметров запроса
        if (!is_array($params) || !count($params)) {
            return false;
        }

        //Проверка перехода пользователя в приложение по запросу приглашения
        if (!isset($params['referrer']) || $params['referrer'] != 'request') {
            return false;
        }
        //Проверка наличия идентификатора пригласившего пользователя
        if (!isset($params['user_id']) || !isset($params['viewer_id'])) {
            return false;
        }
        if (!$params['user_id'] || $params['user_id'] == $params['viewer_id']) {
            return false;
        }

        return true;
    }

    /**
     * Обработка события
     *
     * @param array $params Параметры запроса
     *
     * @return void
     */
    public function handle($params)
    {
        //Создание объекта JSON-RPC клиента
        $client = new JsonClient(SRV_URL, '', FSYS_DEBUG);
        //Выполням запрос установки флага приглашения друга в приложение
        $client->setUserFlag($params['user_id'], SRV_SERVICE_NAME, 4, true);
    }
}
