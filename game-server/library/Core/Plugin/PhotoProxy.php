<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 20.09.12
 * Time: 17:00
 *
 * Плагин прокси к изображениям соц. сетей (обход политики безопасности флэша)
 */
class Core_Plugin_PhotoProxy extends Core_Plugin_Abstract
{

    public function preHandle()
    {
        //Данные запроса
        $query = $_SERVER['QUERY_STRING'];

        //Проверка наличия обращения к прокси фото
        if (!strstr($query, 'image_proxy')) {
            return;
        }
        //Проверка наличия URL фото социальной сети в запросе
        if (!isset($_REQUEST['url'])) {
            return;
        }

        //Получаем URL изображения
        $url = $_REQUEST['url'];
        //Запрос получения изображения
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            die();
        }

        //Отделяем заголовки ответа от тела
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        //Закрываем соединение
        curl_close($ch);

        //Получаем тип контента
        if (preg_match('/Content-Type\: image\/\w+/', $header, $match)) {
            header($match[0]);
        }

        //Отдаем полученное изображение
        echo $body;
        die();
    }

}
