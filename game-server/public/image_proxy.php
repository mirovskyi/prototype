<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.09.12
 * Time: 11:16
 *
 * Проксирование запросов получения изображения из соц. сети
 */

//Получаем URL изображения
$url = $_REQUEST['url'];
if (!$url) {
    die();
}
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