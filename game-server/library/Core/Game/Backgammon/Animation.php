<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.06.12
 * Time: 9:04
 *
 * История анимации для игры "Нарды"
 */
class Core_Game_Backgammon_Animation
{

    /**
     * Типы комманд (действий)
     */
    const THROW_DICE = 'throwdice';
    const MOVE = 'move';
    const THROW_OUT = 'throwout';
    const EMPTY_ACTION = 'empty';

    /**
     * История анимаций
     *
     * @var array
     */
    protected $_history;


    /**
     * Добавление действия бросание костей
     *
     * @param int    $id      Идентификатор команды изменения данных игры
     * @param int    $pos     Позиция игрока за игровым столом
     * @param string $dice    Значение игральных костей
     * @param bool   $canMove Возможность перемещения шашек у игрока
     */
    public function addThrowDiceAction($id, $pos, $dice, $canMove = true)
    {
        $this->_history[$id][] = array(
            'type' => self::THROW_DICE,
            'pos' => $pos,
            'dice' => $dice,
            'move' => intval($canMove)
        );
    }

    /**
     * @param int $id           Идентификатор команды изменения данных игры
     * @param int $fromPosition Начальная позиция шашки
     * @param int $toPosition   Конечная позиция шашки
     */
    public function addMoveAction($id, $fromPosition, $toPosition)
    {
        $this->_history[$id][] = array(
            'type' => self::MOVE,
            'from' => $fromPosition,
            'to' => $toPosition
        );
    }

    /**
     * Добавление действия вывода шашки за пределы игровой доски
     *
     * @param int $id           Идентификатор команды изменения данных игры
     * @param int $fromPosition Позиция шашки
     */
    public function addThrowOutAction($id, $fromPosition)
    {
        $this->_history[$id][] = array(
            'type' => self::THROW_OUT,
            'from' => $fromPosition
        );
    }

    /**
     * Добавление пустого действия
     *
     * @param $id
     */
    public function addEmptyAction($id)
    {
        $this->_history[$id][] = array('type' => self::EMPTY_ACTION);
    }

    /**
     * Получение порядкового номера первой комманды в истории
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
     * Получение порядкового номера последней комманды в истории
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
     * Очистка истории анимации
     *
     * @return void
     */
    public function clear()
    {
        $this->_history = array();
    }

    /**
     * Получение анимации игрового стола
     *
     * @param int                  $id   Порядковый номер комманды обновления игры с которого необходимо отображать анимацию
     * @param Core_Game_Backgammon $game Объект игры
     * @param int                  $pos  Позиция игрока за игровым столом
     *
     * @return string
     */
    public function show($id, Core_Game_Backgammon $game, $pos)
    {
        //Статические данные игры
        $staticData = '';
        //Проверка наличия истории анимации
        if (count($this->_history) <= 1) {
            //Получаем текущие данные игрового стола в виде XML (без данных о игральных костях, т.к. эти данные есть в анимации)
            $staticData = $game->saveXml(false);
        } elseif ($id <= 0 || ($this->getLastCommand() - $id) > 4) {
            //Пользователь вошел в процессе игры либо пропустил более четырех обновлений - отдаем текущие статические данные стола
            return $game->saveXml();
        }

        //Проверка необходимости отображения действия текущего пользователя
        $player = $game->getPlayersContainer()->getIterator()->getElement($pos);
        if ($player == $game->getPlayersContainer()->getActivePlayer()) {
            $action = $game->saveActionXml();
        } else {
            $action = '';
        }

        //Формирование данных игрового стола вместе с анимацией
        return $staticData . $this->saveXml($id) . $action;
    }

    /**
     * Вывод в ответе сервера флага старта игры
     * (выводится только при смене статуса игры с WAIT на PLAY, т.е. когда отрисовывается первая анимация выбрасывания костей)
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
     * Получение истории анимации в виде XML
     *
     * @param int  $id  Порядковый номер комманды обновления игры с которого необходимо отображать анимацию
     *
     * @return string
     */
    public function saveXml($id)
    {
        //Проверка наличия анимации
        if (!count($this->_history)) {
            return '';
        }

        //Проверка наличия обновлений
        $lastId = $this->getLastCommand();
        if ($id >= $lastId) {
            return '';
        }

        //Формирование XML истории
        $xml = new XMLWriter();
        $xml->openMemory();

        for($i = $id + 1; $i <= $lastId; $i++) {
            //Проверка наличия ключа обновления
            if (!isset($this->_history[$i])) {
                continue;
            }
            foreach($this->_history[$i] as $history) {
                //Добавляем элемент комманды
                $xml->startElement($history['type']);
                //Добавляем дополнительные данные комманды
                switch($history['type']) {
                    case self::THROW_DICE: {
                        $xml->writeAttribute('pos', $history['pos']);
                        $xml->writeAttribute('move', $history['move']);
                        $xml->text($history['dice']);
                    }
                        break;
                    case self::MOVE: {
                        $xml->writeAttribute('from', $history['from']);
                        $xml->writeAttribute('to', $history['to']);
                    }
                        break;
                    case self::THROW_OUT: {
                        $xml->writeAttribute('from', $history['from']);
                    }
                        break;
                }
                //Закрываем элемент
                $xml->endElement();
            }
        }

        //Отдаем XML
        return $xml->flush(false);
    }


}
