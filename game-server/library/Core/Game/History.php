<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.05.12
 * Time: 17:07
 *
 * История игр
 */
class Core_Game_History
{

    /**
     * Типы разделов истории
     */
    const DATE_SECTION = 'date';
    const FAVORITE_SECTION = 'favorite';

    /**
     * Максимальное количество записей в разделе избранных игр
     */
    const FAVORITE_RECORDS_COUNT = 30;

    /**
     * Экземпляр класса
     *
     * @var Core_Game_History
     */
    protected static $_instance;

    /**
     * Объект базы данных истории игр
     *
     * @var Core_Game_History_Db_Interface
     */
    protected $_db;


    /**
     * __construct
     */
    protected function __construct()
    {
        //Получение настроек базы данных истории
        $options = Core_Server::getInstance()->getOption('historyDb');
        //Получение типа базы данных
        $dbType = null;
        if ($options && isset($options['name'])) {
            $dbType = $options['name'];
        }
        //Настройки базы данных
        $params = array();
        if ($options && isset($options['params'])) {
            $params = $options['params'];
        }
        //Создание объекта базы данных истории игр
        $db = Core_Game_History_Db::factory($dbType, $params);
        //Установка объекта базы данных
        $this->setDb($db);
    }

    /**
     * Получение экземпляра класса
     *
     * @static
     * @return Core_Game_History
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof Core_Game_History) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Установка объекта базы данных истории игры
     *
     * @param Core_Game_History_Db_Interface $db
     */
    public function setDb(Core_Game_History_Db_Interface $db)
    {
        $this->_db = $db;
    }

    /**
     * Получение объекта базы данных истории игры
     *
     * @return Core_Game_History_Db_Interface
     */
    public function getDb()
    {
        return $this->_db;
    }

    /**
     * Проверка возможности пользователя работать с историей игр (наличие купленной тетради)
     *
     * @param string $idUser  Идентификатор пользователя в соц. сети
     * @param string $network Имя соц. сети
     *
     * @return bool
     */
    public function isAllowHistory($idUser, $network)
    {
        //Заглушка тестовой версии
        //TODO: убрать по завершении работ с историей
        return true;

        //API сервера магазина
        $shop = new Core_Api_DataService_Shop();
        //Получение списка купленных товаров
        $items = $shop->getUserItems($idUser, $network);
        //Проверка наличия купленной услуги записи истории игр
        if (in_array(Core_Shop_Items::GAME_HISTORY, $items)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Добавление данных истории игры
     *
     * @param string $idUser Идентификатор пользователя в соц. сети
     * @param string $network Имя соц. сети
     * @param string $idGame Идентификатор игры
     * @param Core_Game_Abstract $game
     * @return bool
     */
    public function addHitory($idUser, $network, $idGame, Core_Game_Abstract $game)
    {
        //Формирование данных записи в истории для поиска
        $record = new Core_Game_History_Db_Record();
        $record->setIdUser($idUser);
        $record->setNetwork($network);
        $record->setIdGame($idGame);
        $record->setGame($game->getName());
        //Поиск записи
        $result = $this->getDb()->get($record);
        if ($result) {
            $record = $result;
        }

        //Проверка необходимости обновления данных, если данные игры изменены не были ничего не пишем
        if ($game->getCommand() == $record->getCommand()) {
            return true;
        }

        //Обновление текущих данных игры
        $record->setBet($game->getBet());
        $record->setCommand($game->getCommand());
        foreach($game->getPlaces() as $pos => $player) {
            if ($player) {
                $record->setPlayer($pos, $player->getName(), $player->getWinamount());
            }
        }
        //Формирование данных текущего состояния игры
        $data = '<command id="' . $game->getCommand() . '">'
                . $game->saveHistory()
                . '</command>';
        //Запись текущего состяния игры
        $record->addData($data);
        return $this->getDb()->set($record);
    }

    /**
     * Сохранение данных истории пользователя
     *
     * @param string $idUser Идентификатор пользователя в соц. сети
     * @param string $network Имя соц. сети
     * @param string $idGame Идентификатор игры
     * @return bool
     */
    public function saveHistory($idUser, $network, $idGame)
    {
        //Проверка использования кэширования
        if ($this->getDb()->isCached()) {
            //Сохраняем данные из кэша в базу
            return $this->getDb()->commitCache($idGame, $idUser, $network);
        }

        //Данные сразу записывались в базу при добавлении
        return true;
    }

    /**
     * Очистка истории игры из кэша
     *
     * @param string $idGame Идентификатор игры
     *
     * @return bool
     */
    public function clearHistoryCache($idGame)
    {
        if ($this->getDb()->isCached()) {
            return $this->getDb()->deleteCache($idGame);
        }
    }

    /**
     * Удаление записии из дневной истории
     *
     * @param string $date    Дата сохранения записи
     * @param string $idUser  Идентификатор пользователя соц. сети
     * @param string $network Имя соц. сети
     * @param string $idGame  Идентификатор сессии удаляемой игры
     *
     * @return bool
     */
    public function deleteDateHistory($date, $idUser, $network, $idGame)
    {
        //Получение таблицы в БД
        $tableName = $this->getDb()->getTableNameByDate($date);
        //Формирование данных искомой записи
        $record = new Core_Game_History_Db_Record();
        $record->setIdUser($idUser);
        $record->setNetwork($network);
        $record->setIdGame($idGame);
        //Удаление записи
        return $this->getDb()->deleteDb($tableName, $record);
    }

    /**
     * Удаление записии из истории избранных игр
     *
     * @param string $idUser  Идентификатор пользователя соц. сети
     * @param string $network Имя соц. сети
     * @param string $idGame  Идентификатор сессии удаляемой игры
     *
     * @return bool
     */
    public function deleteFavoriteHistory($idUser, $network, $idGame)
    {
        //Получение таблицы в БД
        $tableName = $this->getDb()->getFavoriteTableName();
        //Формирование данных искомой записи
        $record = new Core_Game_History_Db_Record();
        $record->setIdUser($idUser);
        $record->setNetwork($network);
        $record->setIdGame($idGame);
        //Удаление записи
        return $this->getDb()->deleteDb($tableName, $record);
    }

    /**
     * Перенос данных истории игры из раздела игр по датам в раздел избранных
     *
     * @param string $date Дата раздела, в которой находится запись
     * @param string $idUser Идентификатор пользователя в соц. сети
     * @param string $network Имя соц. сети
     * @param string $idGame Идентификатор сессии игры
     *
     * @return bool
     * @throws Core_Exception
     */
    public function moveToFavorite($date, $idUser, $network, $idGame)
    {
        //Проверка возможности добавления записи в избранное
        if ($this->getDb()->getFavoriteRecordsCount() > self::FAVORITE_RECORDS_COUNT) {
            throw new Core_Exception('Maximum records count of favorite games has exceeded', 3051, Core_Exception::USER);
        }

        //Получение таблицы записей по дате
        $dateTable = $this->getDb()->getTableNameByDate($date);
        //Формирование данных искомой записи
        $record = new Core_Game_History_Db_Record();
        $record->setIdUser($idUser);
        $record->setNetwork($network);
        $record->setIdGame($idGame);
        //Получение записи
        $result = $this->getDb()->readDb($dateTable, $record);
        //Проверка наличия результата
        if (!$result) {
            throw new Core_Exception('Record not found', 3052);
        }

        //Обнуляем идентификатор записи
        $result->setId(null);
        //Записываем данные истории игры в таблицу избранных
        return $this->getDb()->writeDb($this->getDb()->getFavoriteTableName(), $result);
    }

}
