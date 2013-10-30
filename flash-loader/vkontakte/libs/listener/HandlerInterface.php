<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 04.10.12
 * Time: 9:19
 *
 * Интерфейс обработчика событий
 */
interface HandlerInterface
{

    /**
     * Проверка наличия события для обработки
     *
     * @param array $params Параметры запроса
     * @return bool
     */
    public function hasEvent($params);

    /**
     * Обработка события
     *
     * @param array $params Параметры запроса
     *
     * @return void
     */
    public function handle($params);

}
