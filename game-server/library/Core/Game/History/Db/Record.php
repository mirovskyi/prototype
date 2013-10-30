<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.05.12
 * Time: 10:06
 *
 * Описание записи в истории игры
 */
class Core_Game_History_Db_Record
{

    /**
     * Идентификатор записи
     *
     * @var mixed
     */
    protected $_id;

    /**
     * Порядковый номер обновления данных игры (команды)
     *
     * @var int
     */
    protected $_command;

    /**
     * Идентификатор пользователя в соц. сети
     *
     * @var string
     */
    protected $_idUser;

    /**
     * Наименование соц. сети
     *
     * @var string
     */
    protected $_network;

    /**
     * Идентификатор игры
     *
     * @var string
     */
    protected $_idGame;

    /**
     * Наименование игры
     *
     * @var string
     */
    protected $_game;

    /**
     * Данные игроков
     *
     * @var string
     */
    protected $_players;

    /**
     * Сумма ставки в игре
     *
     * @var string
     */
    protected $_bet;

    /**
     * Дата
     *
     * @var string
     */
    protected $_date;

    /**
     * Время
     *
     * @var string
     */
    protected $_time;

    /**
     * Данные игры
     *
     * @var string
     */
    protected $_data;


    /**
     * Создание новой записи
     *
     * @param array $options Данные записи
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Установка данных записи
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        foreach($options as $key => $val) {
            $key = strtolower($key);
            switch ($key) {
                case 'id': $this->setId($val); break;
                case 'command': $this->setCommand($val); break;
                case 'id_user': $this->setIdUser($val); break;
                case 'network': $this->setNetwork($val); break;
                case 'id_game': $this->setIdGame($val); break;
                case 'game': $this->setGame($val); break;
                case 'bet': $this->setBet($val); break;
                case 'players': $this->setPlayers($val); break;
                case 'fdate': $this->setDate($val); break;
                case 'ftime': $this->setTime($val); break;
                case 'data': $this->setData($val); break;
            }
        }
    }

    /**
     * Установка идентификатора записи
     *
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Получение идентификатора записи
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Установка порядкового номера команды
     *
     * @param int $command
     */
    public function setCommand($command)
    {
        $this->_command = $command;
    }

    /**
     * Получение порядкового номера команды
     *
     * @return int
     */
    public function getCommand()
    {
        return $this->_command;
    }

    /**
     * Установка идентифиактора пользователя
     *
     * @param string $idUser
     */
    public function setIdUser($idUser)
    {
        $this->_idUser = $idUser;
    }

    /**
     * Получение идентификатора пользователя
     *
     * @return string
     */
    public function getIdUser()
    {
        return $this->_idUser;
    }

    /**
     * Установка наименования соц. сети пользователя
     *
     * @param string $network
     */
    public function setNetwork($network)
    {
        $this->_network = $network;
    }

    /**
     * Получение наименования соц. сети пользователя
     *
     * @return string
     */
    public function getNetwork()
    {
        return $this->_network;
    }

    /**
     * Установка времени записи
     *
     * @param string $time
     */
    public function setTime($time)
    {
        $this->_time = $time;
    }

    /**
     * Получение времени записи
     *
     * @return string
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     * Установка даты записи
     *
     * @param string $date
     */
    public function setDate($date)
    {
        $this->_date = $date;
    }

    /**
     * Получение даты записи
     *
     * @return string
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * Установка идентификатора игры
     *
     * @param string $idGame
     */
    public function setIdGame($idGame)
    {
        $this->_idGame = $idGame;
    }

    /**
     * Получение идентификатора игры
     *
     * @return string
     */
    public function getIdGame()
    {
        return $this->_idGame;
    }

    /**
     * Установка названия игры
     *
     * @param string $game
     */
    public function setGame($game)
    {
        $this->_game = $game;
    }

    /**
     * Получение названия игры
     *
     * @return string
     */
    public function getGame()
    {
        return $this->_game;
    }

    /**
     * Установка суммы ставки в игре
     *
     * @param string $bet
     */
    public function setBet($bet)
    {
        $this->_bet = $bet;
    }

    /**
     * Получение суммы ставки в игре
     *
     * @return string
     */
    public function getBet()
    {
        return $this->_bet;
    }

    /**
     * Установка данных игроков
     *
     * @param string $players
     */
    public function setPlayers($players)
    {
        $this->_players = $players;
    }

    /**
     * Получение данных игроков
     *
     * @return string
     */
    public function getPlayers()
    {
        return $this->_players;
    }

    /**
     * Получение массива данных игроков
     *
     * @return array
     */
    public function getPlayersInfo()
    {
        //Данные о пользователях храняться в json формате
        if (!$this->getPlayers()) {
            return array();
        } else {
            return json_decode($this->getPlayers(), true);
        }
    }

    /**
     * Установка данных игрока
     *
     * @param int    $pos Позиция игрока за игровым столом
     * @param string $playerName Имя игрока
     * @param int    $winamount  Сумма выигрыша игрока
     */
    public function setPlayer($pos, $playerName, $winamount = 0)
    {
        //Получаем массив данных игроков
        $players = $this->getPlayersInfo();
        //Добавляем данные о игроке
        $players[$pos] = array(
            'name' => $playerName,
            'winamount' => $winamount
        );
        //Обновляем данные игроков
        $this->setPlayers(json_encode($players));
    }

    /**
     * Установка данных игры
     *
     * @param string $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * Добавление данных игры
     *
     * @param string $data
     */
    public function addData($data)
    {
        $this->_data .= $data;
    }

    /**
     * Получение данных игры
     *
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

}
