<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 04.10.12
 * Time: 9:51
 *
 * Обработчик события получения подарка
 */
class Gift implements HandlerInterface
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

        //Проверка наличия события подарка
        if (!isset($params['action']) || $params['action'] != 'gift') {
            return false;
        }
        if (!isset($params['user_from']) || !isset($params['viewer_id']) || !isset($params['gift_name'])) {
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
        $client = new JsonClient(SRV_URL, 'gift', FSYS_DEBUG);
        //Выполнение запроса
        $client->confirm(SRV_SERVICE_NAME, $params['user_from'], $params['viewer_id'], $params['gift_name']);
    }

}
