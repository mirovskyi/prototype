<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 22.03.12
 * Time: 14:42
 *
 *
 */
class App_Service_Shop
{

    /**
     * Объект запроса
     *
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * Объект формы добавления нового товара
     *
     * @var App_Form_NewItem
     */
    protected $_newItemForm;


    /**
     * __construct
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function __construct(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
    }

    /**
     * Получение объекта запроса
     *
     * @return Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Получение списка товаров
     *
     * @return App_Model_ShopItem[]
     */
    public function getShopItems()
    {
        $item = new App_Model_ShopItem();
        return $item->fetchAll();
    }

    /**
     * Получение объекта формы добавления нового товара
     *
     * @return App_Form_NewItem
     */
    public function getNewItemForm()
    {
        if (null === $this->_newItemForm) {
            $this->_newItemForm = new App_Form_NewItem();
        }
        return $this->_newItemForm;
    }

    /**
     * Добавление нового товара
     *
     * @return bool|int
     */
    public function addNewItem()
    {
        //Форма добавления нового товара
        $newItemForm = $this->getNewItemForm();
        //Проверка валидности формы добавления нового товара
        if (!$newItemForm->isValid($this->getRequest()->getParams())) {
            return false;
        }

        //Создание нового товара
        $item = new App_Model_ShopItem();
        //Установка данных товара
        $item->setName($newItemForm->getValue('name'));
        $item->setTitle($newItemForm->getValue('title'));
        $item->setPrice($newItemForm->getValue('price'));

        //Сохраняем данные нового товара в прайс
        return $item->save();
    }

}
