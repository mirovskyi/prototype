<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 25.06.12
 * Time: 11:37
 *
 * Описание класса истории анимаций в игре "Шашки"
 */
class Core_Game_Checkers_Animation
{

    /**
     * Доступные действия для анимирования
     */
    const MOVE = 'move';
    const BEAT_OFF = 'beatoff';
    const EMPTY_ANIMATION = 'empty';

    /**
     * История анимаций
     *
     * @var
     */
    protected $_history;


    /**
     * Добавление действия для анимирования
     *
     * @param int         $id           Идентификатор комманды обновления данных игры
     * @param string      $type         Тип действия
     * @param string|null $fromPosition Позиция шашки до действия
     * @param string|null $toPosition   Конечная позиция шашки
     * @param bool        $king         Флаг превращения шашки в "дамку"
     */
    public function addAction($id, $type, $fromPosition = null, $toPosition = null, $king = false)
    {
        $history = array('type' => $type);
        switch ($type) {
            case self::MOVE: {
                $history['from'] = $fromPosition;
                $history['to'] = $toPosition;
                if ($king) {
                    $history['king'] = true;
                }
            }
                break;
            case self::BEAT_OFF: {
                $history['from'] = $fromPosition;
            }
                break;
        }

        //Добавляем идентификатор обновления
        if (!isset($this->_history[$id])) {
            $this->_history[$id] = array();
        }
        //Добавляем данные в историю
        $this->_history[$id][] = $history;
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
     * @param int                $id       Порядковый номер комманды обновления игры с которого необходимо отображать анимацию
     * @param Core_Game_Checkers $game     Объект игры
     *
     * @return string
     */
    public function show($id, Core_Game_Checkers $game)
    {
        //Пользователь вошел в процессе игры либо пропустил более четырех обновлений - отдаем текущие статические данные стола
        if (!count($this->_history) || $id <= 0 || ($this->getLastCommand() - $id) > 4) {
            return $game->saveXml();
        }

        //Формирование данных игрового стола вместе с анимацией
        return $this->saveXml($id);
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
                    case self::MOVE: {
                        $xml->writeAttribute('from', $history['from']);
                        $xml->writeAttribute('to', $history['to']);
                        //Проверка превращения шашки в дамки
                        if (isset($history['king'])) {
                            $xml->writeAttribute('king', 1);
                        } else {
                            $xml->writeAttribute('king', 0);
                        }
                    }
                        break;
                    case self::BEAT_OFF: {
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
