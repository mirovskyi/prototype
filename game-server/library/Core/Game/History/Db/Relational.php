<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.05.12
 * Time: 12:12
 *
 * Реализация работы с реалиционными базами данных истории игр
 */
class Core_Game_History_Db_Relational implements Core_Game_History_Db_Interface
{

    /**
     * Имя таблиц истории по дням
     */
    const DEFAULT_TABLE = 'history';

    /**
     * Имя таблицы истории избранных игр
     */
    const FAVORITE_TABLE = 'favorite';

    /**
     * Флаг использования кэша
     *
     * @var bool
     */
    protected $_cached = false;

    /**
     * Адаптер базы данных
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_adapter;


    /**
     * __construct
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        //Установка настроек
        $this->setOptions($options);
    }

    /**
     * Установка настроек
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        //Проверка наличия настроек кэширования
        if (isset($options['cached'])) {
            $this->setCached($options['cached']);
        }

        //Проверка наличия настроек базы данных
        if (isset($options['adapter'])) {
            //TODO: создание обекта адаптера базы данных из настроек
        } elseif (null === $this->getAdater()) {
            //Проверка наличия ресурса баз данных в загрузчике
            $bootstrap = Core_Server::getInstance()->getBootstrap();
            if ($bootstrap->hasResource('db')) {
                //Получение рсурса базы данных
                $dbResource = $bootstrap->getResource('db');
                //Загрузка ресурса
                $dbResource->bootstrap();
                //Установка адаптера по умолчанию
                $this->setAdapter(Zend_Db_Table::getDefaultAdapter());
            }
        }
    }

    /**
     * Установка флага кэширования
     *
     * @param bool $cached
     */
    public function setCached($cached = true)
    {
        $this->_cached = (bool)$cached;
    }

    /**
     * Флаг записи истории в промежуточное хранилище (кэш)
     *
     * @return boolean
     */
    public function isCached()
    {
        return $this->_cached;
    }

    /**
     * Установка объекта адаптера базы данных
     *
     * @param Zend_Db_Adapter_Abstract $adapter
     */
    public function setAdapter(Zend_Db_Adapter_Abstract $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * Получение объекта адаптера базы данных
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdater()
    {
        return $this->_adapter;
    }

    /**
     * Добавление записи истории
     *
     * @param Core_Game_History_Db_Record $record    Запись истории
     * @param string|null                 $tableName Имя таблицы
     * @return bool
     */
    public function set(Core_Game_History_Db_Record $record, $tableName = null)
    {
        //Проверка флага кэшироания
        if ($this->isCached()) {
            return $this->writeCache($record->getIdGame(), $record);
        }
        //Получение имени таблицы
        if (null === $tableName) {
            //Проверка даты записи
            if ($record->getDate() && $record->getDate() != date('Y-m-d')) {
                //Запись в таблицу за предыдущий день
                $tableName = $this->getPreviousTableName();
            } else {
                //Запись в таблицу за сегодняшний день
                $tableName = $this->getCurrentTableName();
            }
        }
        //Запись в базу данных
        return $this->writeDb($tableName, $record);
    }

    /**
     * Получение записи из истории
     *
     * @param Core_Game_History_Db_Record $record    Данные записи в истории по которым необходимо вести поиск
     * @param string|null                 $tableName Имя таблицы
     * @return Core_Game_History_Db_Record|bool
     */
    public function get(Core_Game_History_Db_Record $record, $tableName = null)
    {
        //Проверка наличия кэширования
        if ($this->isCached()) {
            //Достаем данные из кэша
            $idGame = $record->getIdGame();
            if ($idGame) {
                $result = $this->readCache($idGame);
                if ($result) {
                    return $result;
                }
            }
        }
        //Получение имени таблицы
        if (null === $tableName) {
            //Проверка даты записи
            if ($record->getDate() && $record->getDate() != date('Y-m-d')) {
                //Запись в таблицу за предыдущий день
                $tableName = $this->getPreviousTableName();
            } else {
                //Запись в таблицу за сегодняшний день
                $tableName = $this->getCurrentTableName();
            }
        }
        //Получение данных записи из базы данных
        return $this->readDb($tableName, $record);
    }

    /**
     * Запись данных истории в кэш
     *
     * @param string                      $idGame Идентификатор игры
     * @param Core_Game_History_Db_Record $record Запись в истории
     * @return bool
     */
    public function writeCache($idGame, Core_Game_History_Db_Record $record)
    {
        //Объект работы с кэшом
        $storage = Core_Storage::factory();
        //Запись данных в кэш
        $storage->set($this->_cacheKey($idGame), $record);
    }

    /**
     * Чтение данные истории из кэша
     *
     * @param string $idGame Идентификатор игры
     * @return Core_Game_History_Db_Record|bool
     */
    public function readCache($idGame)
    {
        //Объект работы с кэшом
        $storage = Core_Storage::factory();
        //Получение записи из кэша
        return $storage->get($this->_cacheKey($idGame));
    }

    /**
     * Удаление записи из кэша
     *
     * @param string $idGame Идентификатор игры
     * @return bool
     */
    public function deleteCache($idGame)
    {
        //Объект работы с кэшом
        $storage = Core_Storage::factory();
        //Удаление записи из кэша
        return $storage->delete($this->_cacheKey($idGame));
    }

    /**
     * Запись данных истории из базы данных
     *
     * @param string                      $tableName Имя таблицы
     * @param Core_Game_History_Db_Record $record    Данные записи в истории
     * @return bool
     */
    public function writeDb($tableName, Core_Game_History_Db_Record $record)
    {
        //Проверка наличия таблицы
        $this->_checkTable($tableName);
        //Формирование данных записи в таблицу
        $tableRecord = array();
        if (null !== $record->getId()) {
            $tableRecord['id'] = $record->getId();
        }
        if (null !== $record->getCommand()) {
            $tableRecord['command'] = $record->getCommand();
        }
        if (null !== $record->getIdUser()) {
            $tableRecord['id_user'] = $record->getIdUser();
        }
        if (null !== $record->getNetwork()) {
            $tableRecord['network'] = $record->getNetwork();
        }
        if (null !== $record->getIdGame()) {
            $tableRecord['id_game'] = $record->getIdGame();
        }
        if (null !== $record->getGame()) {
            $tableRecord['game'] = $record->getGame();
        }
        if (null !== $record->getPlayers()) {
            $tableRecord['players'] = $record->getPlayers();
        }
        if (null !== $record->getBet()) {
            $tableRecord['bet'] = $record->getBet();
        }
        if (null !== $record->getDate()) {
            $tableRecord['fdate'] = $record->getDate();
        }
        if (null !== $record->getTime()) {
            $tableRecord['ftime'] = $record->getTime();
        }
        if (null !== $record->getData()) {
            $tableRecord['data'] = $record->getData();
        }
        //Проверка наличия id записи
        if (null !== $record->getId()) {
            //Обновляем записи в базе данных
            return $this->getAdater()->update($tableName, $tableRecord, 'id = ' . $tableRecord['id']);
        } else {
            //Добавление текущих даты/времени
            $tableRecord['fdate'] = date('Y-m-d');
            $tableRecord['ftime'] = date('H:i:s');
            //Добавление записи в базе данных
            return $this->getAdater()->insert($tableName, $tableRecord);
        }
    }

    /**
     * Чтение данных истории из базы данных
     *
     * @param string                      $tableName Имя таблицы
     * @param Core_Game_History_Db_Record $record    Данные записи по которым необходимо вести поиск
     * @return Core_Game_History_Db_Record|bool
     */
    public function readDb($tableName, Core_Game_History_Db_Record $record)
    {
        //Проверка наличия таблицы
        $this->_checkTable($tableName);
        //Объект запроса к БД
        $select = $this->getAdater()->select();
        //Формирование запроса
        if (null !== $record->getId()) {
            $select->where('id = ?', $record->getId());
        }
        if (null !== $record->getCommand()) {
            $select->where('command = ?', $record->getCommand());
        }
        if (null !== $record->getIdUser()) {
            $select->where('id_user = ?', $record->getIdUser());
        }
        if (null !== $record->getNetwork()) {
            $select->where('network = ?', $record->getNetwork());
        }
        if (null !== $record->getIdGame()) {
            $select->where('id_game = ?', $record->getIdGame());
        }
        if (null !== $record->getGame()) {
            $select->where('game = ?', $record->getGame());
        }
        if (null !== $record->getPlayers()) {
            $select->where('players = ?', $record->getPlayers());
        }
        if (null !== $record->getBet()) {
            $select->where('bet = ?', $record->getBet());
        }
        if (null !== $record->getDate()) {
            $select->where('fdate = ?', $record->getDate());
        }
        if (null !== $record->getTime()) {
            $select->where('ftime = ?', $record->getTime());
        }
        //Установка таблицы
        $select->from($tableName);
        //Выполнение запроса
        $query = $select->query(Zend_Db::FETCH_ASSOC);
        //Проверка наличия результата
        if (!$query->rowCount()) {
            return false;
        }
        //Получение данных записи
        $row = $query->fetch();
        //Возвращаем данные записи
        return new Core_Game_History_Db_Record($row);
    }

    /**
     * Удаление данных истории из базы
     *
     * @param string                      $tableName Имя таблицы
     * @param Core_Game_History_Db_Record $record    Данные записи, которую необходимо удалить
     * @return bool
     */
    public function deleteDb($tableName, Core_Game_History_Db_Record $record)
    {
        //Проверка наличия таблицы
        $this->_checkTable($tableName);
        //Проверка наличия id записи
        if (null === $record->getId()) {
            return false;
        }

        //Удаляем запись
        return $this->getAdater()->delete($tableName, 'id = ' . $record->getId());
    }

    /**
     * Фиксация данных истории из кэша в базу данных
     *
     * @param string $idGame Идентификтор игры
     * @param string $idUser Идентификатор пользователя соц. сети, для которого необходимо сохранить историю
     * @param string $network Имя соц. сети
     *
     * @return bool
     */
    public function commitCache($idGame, $idUser, $network)
    {
        //Получение данных из кэша
        $record = $this->readCache($idGame);
        if (!$record) {
            return false;
        }
        //Установка данных пользвателя, для которого сохраняется история
        $record->setIdUser($idUser);
        $record->setNetwork($network);
        //Запись в базу данных
        return $this->writeDb($this->getCurrentTableName(), $record);
    }

    /**
     * Получение списка игр
     *
     * @param string $tableName Имя таблицы
     * @param string $idUser    Идентификатор пользователя в соц. сети
     * @param string $network   Имя соц. сети
     * @return array КЛЮЧ МАССИВА - НАЗВАНИЕ ИГРЫ, ЗНАЧЕНИЕ - КОЛИЧЕСТВО СЫГРАННЫХ ПАРТИЙ
     */
    public function getGames($tableName, $idUser, $network)
    {
        //Проверка наличия таблицы
        $this->_checkTable($tableName);
        //Список игр
        $games = array();
        //Объект запроса к БД
        $select = $this->getAdater()->select();
        //Формирование запроса
        $countExpr = new Zend_Db_Expr('COUNT(*)');
        $select->from($tableName, array('game', 'game_count' => $countExpr))
               ->where('id_user = ?', $idUser)
               ->where('network = ?', $network)
               ->group('game');
        //Выполнение запроса
        $query = $select->query(Zend_Db::FETCH_OBJ);
        //Проверка результата
        if ($query->rowCount()) {
            //Формирование списка
            foreach($query->fetchAll() as $item) {
                $games[$item->game] = $item->game_count;
            }
        }

        //Возвращаем список игр
        return $games;
    }

    /**
     * Получение списка записей в истории игры
     *
     * @param string $tableName Имя таблицы
     * @param string $idUser    Идентификатор пользователя в соц. сети
     * @param string $network   Имя соц. сети
     * @param string $game      Наименование игры
     * @return Core_Game_History_Db_Record[]
     */
    public function getGameRecords($tableName, $idUser, $network, $game)
    {
        //Проверка наличия таблицы
        $this->_checkTable($tableName);
        //Список игр
        $games = array();
        //Объект запроса к БД
        $select = $this->getAdater()->select();
        //Формирование запроса
        $select->from($tableName)
               ->where('id_user = ?', $idUser)
               ->where('network = ?', $network)
               ->where('game = ?', $game);
        //Выполнение запроса
        $query = $select->query(Zend_Db::FETCH_ASSOC);
        //Проверка результата запроса
        if ($query->rowCount()) {
            //Формирование списка игр
            foreach($query->fetchAll() as $item) {
                //Создание объекта записи истории
                $record = new Core_Game_History_Db_Record($item);
                //Добавляем запись в список игр
                $games[] = $record;
            }
        }

        //Возвращаем список записей в истории игр
        return $games;
    }

    /**
     * Получение количество записей в разделе избранны игр
     *
     * @return int
     */
    public function getFavoriteRecordsCount()
    {
        //Проверка наличия таблицы
        $this->_checkTable($this->getFavoriteRecordsCount());
        //Объект запроса к БД
        $select = $this->getAdater()->select();
        //Формирование запроса
        $select->from(
            $this->getFavoriteRecordsCount(),
            array('count' => new Zend_Db_Expr('COUNT(*)'))
        );
        //Выполнение запроса
        $query = $select->query(Zend_Db::FETCH_OBJ);
        //Проверка результата
        if ($query->rowCount()) {
            return $query->fetch()->count;
        } else {
            return false;
        }
    }

    /**
     * Получение имени таблицы с записями истории за текущий день
     *
     * @return string
     */
    public function getCurrentTableName()
    {
        return self::DEFAULT_TABLE . '_' . date('Ymd');
    }

    /**
     * Получение имени таблицы с записями истории за предыдущий день
     *
     * @return string
     */
    public function getPreviousTableName()
    {
        $date = new DateTime();
        $date->modify('-1 days');
        return self::DEFAULT_TABLE . '_' . $date->format('Ymd');
    }

    /**
     * Получение имени таблицы с избранными записями в истории
     *
     * @return string
     */
    public function getFavoriteTableName()
    {
        return self::FAVORITE_TABLE;
    }

    /**
     * Получение ключа записи в кэше
     *
     * @param string $id
     * @return string
     */
    private function _cacheKey($id)
    {
        return 'history::' . $id;
    }

    /**
     * Проверка наличия таблицы в БД (создание в случае отсутствия)
     *
     * @param string $tableName Имя таблицы
     * @return void
     */
    private function _checkTable($tableName)
    {
        //Проверка наличия таблицы в БД
        if (in_array($tableName, $this->getAdater()->listTables())) {
            return;
        }

        //Создание таблицы
        $sql = 'CREATE TABLE ' . $tableName . '('
               . ' id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,'
               . ' command INT(11) NOT NULL DEFAULT 0,'
               . ' id_user varchar(25) NOT NULL DEFAULT \'0\','
               . ' network varchar(25) NOT NULL DEFAULT \'\','
               . ' id_game varchar(32) NOT NULL DEFAULT \'\','
               . ' game varchar(25) NOT NULL DEFAULT \'\','
               . ' players text NOT NULL DEFAULT \'\','
               . ' bet INT(11) NOT NULL DEFAULT 0,'
               . ' fdate DATE NOT NULL DEFAULT \'0000-00-00\','
               . ' ftime TIME NOT NULL DEFAULT \'00:00:00\','
               . ' data text,'
               . ' KEY user (id_user, network),'
               . ' KEY id_game (id_game)'
               . ') ENGINE InnoDB DEFAULT CHARSET utf8';
        $this->getAdater()->query($sql);
    }

    /**
     * Получение имени тблицы истории за указанную дату
     *
     * @param string $date Дата в формате 'Y-m-d'
     *
     * @return string
     */
    public function getTableNameByDate($date)
    {
        //Изменение формата даты
        $date = str_replace('-', '', $date);
        //Формирование имени таблицы истории
        $tableName = self::DEFAULT_TABLE . '_' . $date;

        return $tableName;
    }
}
