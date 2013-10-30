<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 22.03.12
 * Time: 14:14
 * To change this template use File | Settings | File Templates.
 */
class ShopController extends Zend_Controller_Action
{

    /**
     * Объект бизнес-логики
     *
     * @var App_Service_Shop
     */
    protected $_service;

    /**
     * Инициализация контроллера
     */
    public function init()
    {
        $this->_service = new App_Service_Shop($this->getRequest());
    }

    /**
     * Получение объекта бизнес-логики
     *
     * @return App_Service_Shop
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * Главная страница
     */
    public function indexAction()
    {
        $this->view->assign(array(
            'items' => $this->getService()->getShopItems(),
            'newItemForm' => $this->getService()->getNewItemForm()
        ));
    }

    /**
     * Метод добавления товара
     */
    public function additemAction()
    {
        if ($this->getService()->addNewItem()) {
            $this->_helper->FlashMessenger('Товар успешно добавлен в базу');
        } else {
            $this->_helper->FlashMessenger('Ошибка записи данных');
        }

        //Редирект на главную
        $this->_helper->getHelper('Redirector')->gotoSimple('index', 'shop');
    }

}
