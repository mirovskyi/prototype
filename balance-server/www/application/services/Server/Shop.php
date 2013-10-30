<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 15.03.12
 * Time: 15:49
 *
 * Класс модели работы с магазином игрового сервиса
 */
class App_Service_Server_Shop
{

    /**
     * Идентификатор пользователя в соц. сети
     *
     * @var string
     */
    protected $_idServiceUser;

    /**
     * Имя сервиса соц. сети
     *
     * @var string
     */
    protected $_nameService;

    /**
     * Объект модели пользователя
     *
     * @var App_Model_User
     */
    protected $_user;

    /**
     * Объект модели соц. сети
     *
     * @var App_Model_Service
     */
    protected $_service;

    /**
     * Наименвание последнем покупки
     *
     * @var string
     */
    protected $_lastBuyName;


    /**
     * Создание нового объекта
     *
     * @param string $idServiceUser Идентификатр пользователя в соц. сети
     * @param string $nameService Имя сервиса соц. сети
     */
    public function __construct($idServiceUser = null, $nameService = null)
    {
        $this->_idServiceUser = $idServiceUser;
        $this->_nameService = $nameService;
    }

    /**
     * Установка объекта модели пользователя
     *
     * @param App_Model_User $user
     * @return App_Service_Server_Shop
     */
    public function setUser(App_Model_User $user)
    {
        $this->_user = $user;
        return $this;
    }

    /**
     * Получение объекта модели пользователя
     *
     * @return App_Model_User
     */
    public function getUser()
    {
        if (null === $this->_user) {
            //Создание объекта модели пользователя
            $user = new App_Model_User();
            //Запрос поиска пользователя
            $where = $user->select()->where('id_service = ?', $this->getService()->getId())
                                    ->where('id_service_user = ?', $this->_idServiceUser);
            //Поиск данных и установка объекта модели пользователя
            $this->setUser($user->fetchRow($where));
        }

        return $this->_user;
    }

    /**
     * Установка объекта модели соц. сети
     *
     * @param App_Model_Service $service
     *
     * @return App_Service_Server_Shop
     */
    public function setService(App_Model_Service $service)
    {
        $this->_service = $service;
        return $this;
    }

    /**
     * Получение объекта модели соц. сети
     *
     * @return App_Model_Service
     */
    public function getService()
    {
        if (null === $this->_service) {
            $service = new App_Model_Service();
            $where = $service->select()->where('name = ?', $this->_nameService);
            $this->setService($service->fetchRow($where));
        }
        return $this->_service;
    }

    /**
     * Получение объекта модели товара магазина
     *
     * @param string $name     Наименование товара
     * @param int    $count    Количество едениц товара
     * @param int    $lifetime Срок действия товара
     * @return App_Model_ShopItem|bool
     */
    public function getShopItem($name, $count = null, $lifetime = null)
    {
        //Получаем данные товара
        $item = new App_Model_Item();
        $where = $item->select()->where('name = ?', $name);
        if (!$item->fetchRow($where)->getId()) {
            return false;
        }

        //Поиск товара в магазине
        $shopItem = new App_Model_ShopItem();
        $where = $shopItem->select()->where('id_item = ?', $item->getId());
        if (null !== $count) {
            $where->where('item_count = ?', $count);
        }
        if (null !== $lifetime) {
            $where->where('lifetime = ?', $lifetime);
        }

        if ($shopItem->fetchRow($where)->getId()) {
            return $shopItem;
        } else {
            return false;
        }
    }

    /**
     * Получение списка товаров магазина
     *
     * @param array $filters Правила фильтрации списка товаров
     *
     * @return array
     */
    public function getShopItems($filters = array())
    {
        //Массив товаров
        $arrShopItems = array();

        //Получение списка всех активных товаров магазина
        $shopItems = $this->_getShopItems($filters);
        //Формрование списка товаров и данных едениц товара в магазине
        foreach($shopItems as $itemInfo) {
            //Наименование товара
            $name = $itemInfo['name'];
            //Проверка наличия наименования товара в списке
            if (!isset($arrShopItems[$name])) {
                $arrShopItems[$name] = array();
            }
            //Формирование срока действия товара
            $itemInfo['hours'] = floor($itemInfo['lifetime'] / 3600);
            $itemInfo['days'] = floor($itemInfo['hours'] / 24);
            //Корректировка часов
            $itemInfo['hours'] = $itemInfo['hours'] - ($itemInfo['days'] * 24);
            //Удаление лишней инфы
            unset($itemInfo['name']);
            unset($itemInfo['title']);
            //Добавление данных о еденице товара в магазине
            $arrShopItems[$name][] = $itemInfo;
        }

        //Возвращаем список данных товаров магазина
        return $arrShopItems;
    }

    /**
     * Получение списка товаров, купленных пользователем
     *
     * @return array
     */
    public function getUserItems()
    {
        //Массив данных о товарах пользователя
        $arrUserItems = array();
        //Формрование списка купленных пользователем товаров
        foreach($this->_getUserItems() as $itemInfo) {
            //Проверка истечения срока действия товара
            if ($itemInfo['deadline'] < time() &&  $itemInfo['deadline'] != 0) {
                //Удаление товара из списка купленных пользователем
                $userItem = new App_Model_UserItem();
                $userItem->delete('id = ' . $itemInfo['id']);
            } else {
                //Добавляем данные товара в список купленных пользователем
                $arrUserItems[$itemInfo['name']] = array(
                    'name' => $itemInfo['name'],
                    'title' => $itemInfo['title'],
                    'date' => $itemInfo['buy_date'],
                    'deadline' => $itemInfo['deadline']
                );
            }
        }

        //Возвращаем список товаров пользователя
        return $arrUserItems;
    }

    /**
     * Получение списка товаров магазина с данными о покупках пользователя
     *
     * @param array $filters Правила фильтрации списка товаров
     *
     * @return array
     */
    public function getUserShopItems($filters = array())
    {
        //Получаем все товары магазина
        $shopItems = $this->getShopItems($filters);
        //Получаем товары пользователя
        $userItems = $this->getUserItems();

        //Добавление в данные товара информацию о покупках пользователя
        $arrResult = array();
        foreach($shopItems as $name => $itemInfo) {
            //Проверка покупки товара пользователем
            if (isset($userItems[$name])) {
                //Установка флага покупки
                $sold = 1;
                //Считаем сколько секунд осталось до окончания срока лействия товара
                $exptime = ($userItems[$name]['deadline']) ? $userItems[$name]['deadline'] - time() : 0;
            } else {
                //Товар не был куплен
                $sold = 0;
                $exptime = 0;
            }
            //Добавление данных товара
            $arrResult[] = array(
                'name' => $name,
                'sold' => $sold,
                'exptime' => $exptime,
                'prices' => $itemInfo
            );
        }

        //Возвращаем данные о товарах
        return $arrResult;
    }

    /**
     * Получение объекта купленного товара пользователем
     *
     * @param App_Model_ShopItem|string $item Имя товара
     *
     * @return App_Model_UserItem|bool
     * @throws Zend_Exception
     */
    public function getUserItem($item)
    {
        if (is_string($item)) {
            //Наименование товара
            $itemName = $item;
            //Проверяем наличие товара в магазине
            $item = new App_Model_Item();
            $where = $item->select()->where('name = ?', $itemName);
            if ($item->fetchRow($where)->getId() == null) {
                throw new Zend_Exception('Item \'' . $item . '\' does not exists');
            }
        }

        $userItem = new App_Model_UserItem();
        $where = $userItem->select()
            ->where('id_user = ?', $this->getUser()->getId())
            ->where('id_item = ?', $item->getId());
        if (null !== $userItem->fetchRow($where)->getId()) {
            return $userItem;
        } else {
            return false;
        }
    }

    /**
     * Покупка товара магазина пользователем (Добавление товара из магазина пользователю)
     *
     * @param int  $shopItemId    Идентификатор товара в магазине (shop_items)
     * @param bool $withdrawChips Флаг необходимости списания стоимости товара с баланса пользователя (фишки)
     * @return bool
     * @throws Zend_Exception
     */
    public function buy($shopItemId, $withdrawChips = false)
    {
        //Получаем данные товара
        $shop = new App_Model_ShopItem();
        if (null == $shop->find($shopItemId)) {
            throw new Zend_Exception('Shop item \'' . $shopItemId . '\' does not exists', 4001);
        }

        $item = new App_Model_Item();
        if (null == $item->find($shop->getIdItem())->getId()) {
            throw new Zend_Exception('Item \'' . $shop->getIdItem() . '\' does not exists', 4001);
        }

        //Если покупка фишек, увеличиваем баланс игрока
        if ($item->getName() == App_Model_Item::CHIPS) {

            //Получаем текущий баланс игрока
            $userBalance = $this->getUser()->getBalance();
            //Увеличиваем баланс игрока
            $this->getUser()->setBalance($userBalance + $shop->getItemCount());
            if (!$this->getUser()->save()) {
                throw new Zend_Exception('Can\'t increase user balance');
            }
            //Установка пользователю флаг покупки фишек
            $info = new App_Service_Server_Info();
            $info->switchUserFlag($this->getUser(), 5, true);
            //Установка наименования последней покупки
            $this->setLastBuyName($item->getName() . $shop->getItemCount());
        } else {
            //Если установлен флаг списания стоимости фишками со счета пользователя, проверяем достаточно ли фишек
            if ($withdrawChips) {
                //Получаем текущий баланс игрока
                $userBalance = $this->getUser()->getBalance();
                //Проверка наличия достаточной суммы
                if ($shop->getChips() > $userBalance) {
                    throw new Zend_Exception('insufficient user balance for buy item \''. $item->getName() . '\'', 4000);
                }
            }

            //Добавляем товар в покупки игрока
            $useritems = new App_Model_UserItem();
            $where = $useritems->select()->where('id_item = ?', $item->getId())
                                         ->where('id_user = ?', $this->getUser()->getId());
            $useritems->fetchRow($where);
            //Проверка на существования покупки с неограниченым сроком действия
            if ($useritems->getId() && !$useritems->getDeadline()) {
                throw new Zend_Exception('User allready has the item \'' . $item->getName() . '\'', 4002);
            }

            //Формируем дедлайн товара
            if ($shop->getLifetime() > 0) {
                if ($useritems->getDeadline() && $useritems->getDeadline() > time()) {
                    $deadline = $useritems->getDeadline() + $shop->getLifetime();
                } else {
                    $deadline = time() + $shop->getLifetime();
                }
            } else {
                $deadline = 0;
            }

            //Старт транзакции
            $this->_getDbAdapter()->beginTransaction();

            //Списания средств со счета пользователя
            if ($withdrawChips) {
                $this->getUser()->setBalance($this->getUser()->getBalance() - $shop->getChips());
                if (!$this->getUser()->save()) {
                    $this->_getDbAdapter()->rollBack();
                    throw new Zend_Exception('Can not update user balance in database');
                }
            }

            //Добавляем товар в покупки игрока
            $useritems->setIdItem($item->getId())
                ->setIdUser($this->getUser()->getId())
                ->setDeadline($deadline)
                ->setBuyDate(date('Y-m-d H:i:s'));
            if (!$useritems->save()) {
                $this->_getDbAdapter()->rollBack();
                throw new Zend_Exception('Can not insert new user item to database');
            }

            //Подтверждение транзакции
            $this->_getDbAdapter()->commit();

            //Установка наименования последней покупки
            $this->setLastBuyName($item->getName());
        }

        return true;
    }

    /**
     * Установка наименование последней покупки
     *
     * @param string $name
     */
    public function setLastBuyName($name)
    {
        $this->_lastBuyName = $name;
    }

    /**
     * Получение наименования последней покупки
     *
     * @return string
     */
    public function getLastBuyName()
    {
        return $this->_lastBuyName;
    }

    /**
     * Метод получения выборки данных товаров магазина
     *
     * @param array $filters Правила фильтрации списка товаров
     *
     * @return array
     */
    private function _getShopItems($filters = array())
    {
        //Формирование запроса
        $query = $this->_getDbAdapter()->select();
        $query->from(array('i' => 'items'), array('name', 'title'))
              ->join(array('s' => 'shop_items'), 'i.id = s.id_item',
                     array('id','chips','money','count' => 'item_count', 'lifetime'))
              ->where('i.active = 1')
              ->where('s.id_service = ?', $this->getService()->getId());
        //Правила фильтрации
        if (isset($filters['name'])) {
            $query->where('i.name = ?', $filters['name']);
        }

        //Выполнение запроса, возвращаем результат
        return $query->query(Zend_Db::FETCH_ASSOC)->fetchAll();
    }

    /**
     * Метод получения выборки данных купленных пользовтаелем товаров
     *
     * @return array
     */
    private function _getUserItems()
    {
        //Формирование запроса
        $query = $this->_getDbAdapter()->select();
        $query->from(array('i' => 'items'), array('name', 'title'))
              ->join(array('u' => 'user_items'), 'i.id = u.id_item', array('id','deadline','buy_date'))
              ->where('u.id_user = ?', $this->getUser()->getId())
              ->where('i.active = 1');

        //Выполнение запроса, возвращаем результат
        return $query->query(Zend_Db::FETCH_ASSOC)->fetchAll();
    }

    /**
     * Получение объекта адаптера БД
     *
     * @return Zend_Db_Adapter_Abstract
     * @throws Core_Exception
     */
    private function _getDbAdapter()
    {
        if (Zend_Db_Table_Abstract::getDefaultAdapter()) {
            return Zend_Db_Table_Abstract::getDefaultAdapter();
        }

        //Получение ресурса адаптера БД
        $bootstrap = Core_Server::getInstance()->getBootstrap();
        if ($bootstrap->hasResource('db')) {
            return $bootstrap->getResource('db')->bootstrap();
        } else {
            throw new Core_Exception('Database connection error');
        }
    }
}
