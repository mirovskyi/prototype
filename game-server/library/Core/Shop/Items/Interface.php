<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 26.04.12
 * Time: 17:21
 *
 * Интерфейс списка товаров из магазина
 */
interface Core_Shop_Items_Interface
{

    /**
     * Установка списка товаров
     *
     * @abstract
     * @param array $items
     */
    public function setItems(array $items);

    /**
     * Добавление товара
     *
     * @abstract
     * @param string $itemName
     */
    public function addItem($itemName);

    /**
     * Получение списка товаров
     *
     * @abstract
     * @return array
     */
    public function getItems();

    /**
     * Проверка наличия товара
     *
     * @abstract
     * @param string $itemName
     * @return bool
     */
    public function hasItem($itemName);

}
