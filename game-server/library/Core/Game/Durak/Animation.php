<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 30.04.12
 * Time: 12:53
 *
 * Класс истории действий последнего хода в игре, для отрисовки анимации на стороне клиента
 */
class Core_Game_Durak_Animation
{

    /**
     * Типы команд
     */
    const DEAL = 'deal';
    const THROWIN = 'throwin';
    const BEATOFF = 'beatoff';
    const TAKE = 'take';
    const CLEAR = 'clear';
    const GOCARD = 'gocard';
    const EMPTY_ANIMATION = 'empty';

    /**
     * Флаг ручного обновления данных игры при тестах
     */
    const QA_UPDATE = 'qa';

    /**
     * История движения карт за игровым столом
     *
     * @var array
     */
    protected $_history = array();


    /**
     * Добавление данных хода в историю
     *
     * @param int $id Порядковый номер обновления данных
     * @param string $type Тип команды
     * @param int|null $pos Идентификатор позиции игрока, к которому относится команда
     * @param string|array|null $cards Данные карт
     */
    public function addAction($id, $type, $pos = null, $cards = null)
    {
        $history = array('type' => $type);
        if ($pos) {
            $history['pos'] = $pos;
        }

        switch ($type) {
            case self::DEAL:
            case self::THROWIN:
            case self::GOCARD: {
                $history['card'] = $cards;
            }
            break;
            case self::BEATOFF: {
                if (is_array($cards)) {
                    $cards = implode(':', $cards);
                }
                $history['card'] = $cards;
            }
            break;
        }

        //Добавляем идентификатор обновления
        if (!isset($this->_history[$id])) {
            $this->_history[$id] = array();
        }
        //Добавление данных истории
        $this->_history[$id][] = $history;
    }

    /**
     * Получение порядкового номера первой комманды
     *
     * @return int
     */
    public function getFirstCommand()
    {
        $commands = array_keys($this->_history);
        if (!count($commands)) {
            return 0;
        }

        return $commands[0];
    }

    /**
     * Получение порядкового номера последней команлды
     *
     * @return int
     */
    public function getLastCommand()
    {
        $commands = array_keys($this->_history);
        if (!count($commands)) {
            return 0;
        }

        return $commands[count($commands) - 1];
    }

    /**
     * Очистка данных объекта
     *
     * @return void
     */
    public function clear()
    {
        $this->_history = array();
    }

    /**
     * Отображение истории обновления игрового стола (анимации)
     *
     * @param int|null $id Идентификатор обновления игры с которого необходимо отобразить анимацию
     * @param Core_Game_Durak $game Объект данных игрового стола
     * @param int|null $pos Позиция игрока для которого необходимо отобразить анимацию
     * @return string
     */
    public function show($id, Core_Game_Durak $game, $pos = null)
    {
        //Проверка наличия изменений в игре
        if (!$this->getFirstCommand()) {
            return '';
        }

        //Если в следующей комманде было обновление данных "вручную" (тестирование) - отдаем статические данные для перерисовки данных стола
        $nextCommandId = $id + 1;
        if (isset($this->_history[$nextCommandId])) {
            //Получаем первый элемент массива (первую анимацию в списке)
            $animationData = $this->_history[$nextCommandId][0];
            //Проверка флага обновления при тестировании
            if ($animationData['type'] == self::QA_UPDATE) {
                //Отдаем статические данные игры
                return $game->saveXml($pos);
            }
        }

        //Статические данные игрового стола
        $staticData = '';
        //В случае, если в истории анимации только одна команда обновления данных
        //показываем данные розыгрыша (без данных о картах игроков, т.к. первая команда всегда раздача карт)
        if ($this->getFirstCommand() == $this->getLastCommand() || ($id > 0 && $id < $this->getFirstCommand())) {
            //Проверка наличия анимации в текущей команде (нельзя сбрасывать данные о картах игроков без анимации раздачи)
            $current = $game->getCommand();
            if (isset($this->_history[$current]) && count($this->_history[$current])) {
                $staticData = $game->saveXml($pos, false);
            }
        }
        //Если переданный идентификатор изменения равен нулю, либо отстал от последнего номера изменения более чем на 4 пункта,
        //показываем все данные розыгрыша без анимации
        elseif ($id <= 0 || ($this->getLastCommand() - $id) > 4) {
            return $game->saveXml($pos);
        }

        //Получаем данные анимации в виде XML
        return $staticData . $this->saveXml($id, $pos);
    }

    /**
     * Вывод в ответе сервера флага старта игры
     * (выводится только при смене статуса игры с WAIT на PLAY, т.е. когда отрисовывается первая анимация раздачи карт)
     *
     * @return string
     */
    public function showStart()
    {
        if (count($this->_history) == 1) {
            return '<start />';
        }
    }

    /**
     * Получение данных истории в виде XML
     *
     * @param int $id Порядковый номер обновления данных, с которого необходимо начинать вывод
     * @param int|null $pos Позиция текущего пользователя
     * @return mixed|string
     */
    public function saveXml($id, $pos = null)
    {
        if (!count($this->_history)) {
            return '';
        }

        //Получаем последний номер обновления
        $commandIds = array_keys($this->_history);
        $lastId = $commandIds[count($commandIds) - 1];
        //Проверка наличия новых данных
        if ($lastId <= $id) {
            return '';
        }

        //Формировние XML истории
        $xml = new XMLWriter();
        $xml->openMemory();
        for($i = $id + 1; $i <= $lastId; $i++) {
            //Проверка наличия ключа обновления
            if (!isset($this->_history[$i])) {
                continue;
            }
            //Формирование данных истории
            foreach($this->_history[$i] as $history) {
                //Флаг "тестирования" не отображать

                //Для наблюдателей и игроков на указанной позиции GOCARD не отображается
                if ($history['type'] == self::GOCARD && ($pos === null || $pos == $history['pos'])) {
                    continue;
                }
                //Добавление элемента команды
                $xml->startElement($history['type']);
                //Добавление атрибутов элемента команды
                switch ($history['type']) {
                    case self::DEAL: {
                        //Позиция игрока
                        $xml->writeAttribute('pos', $history['pos']);
                        //Карта, если чужая, то закрытая
                        if ($pos !== null && $pos != $history['pos']) {
                            $xml->writeAttribute('card', 'X');
                        } else {
                            $xml->writeAttribute('card', $history['card']);
                        }
                    }
                    break;
                    case self::THROWIN:
                    case self::BEATOFF:
                    case self::GOCARD: {
                        //Позиция игрока
                        $xml->writeAttribute('pos', $history['pos']);
                        //Данные карт(ы)
                        $xml->writeAttribute('card', $history['card']);
                    }
                    break;
                    case self::TAKE: {
                        //Позиция игрока
                        $xml->writeAttribute('pos', $history['pos']);
                        //Флаг скрытия карт у игрока
                        if ($pos !== null && $pos != $history['pos']) {
                            $xml->writeAttribute('hide', 1);
                        } else {
                            $xml->writeAttribute('hide', 0);
                        }
                    }
                    break;
                }
                $xml->endElement();
            }
        }

        //Возвращаем полученный XML
        return $xml->flush(false);
    }

}