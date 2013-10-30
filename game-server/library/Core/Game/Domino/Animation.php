<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.06.12
 * Time: 9:04
 *
 * История анимации для игры "Домино"
 */
class Core_Game_Domino_Animation
{

    /**
     * Типы комманд (действий)
     */
    const DEAL = 'deal';
    const THROWIN = 'throwin';
    const FISH = 'fish';
    const EMPTY_ANIMATION = 'empty';

    /**
     * Флаг ручного обновления данных игры при тестах
     */
    const QA_UPDATE = 'qa';

    /**
     * Стороны ряда костей на игровом столе
     */
    const SIDE_LEFT = 'L';
    const SIDE_RIGHT = 'R';

    /**
     * История анимаций
     *
     * @var array
     */
    protected $_history;


    /**
     * Добавление комманды (действия) в историю анимации
     *
     * @param int         $id   Порядковый номер комманды обновления данных игры
     * @param string      $type Тип комманды (действия)
     * @param int|null    $pos  Позиция игрока за игровым столом который совершил действие
     * @param string|null $bone Данные игровой кости
     * @param string|null $side Сторона ряда костей на игровом столе к которой добаляется еще одна кость (только при THROWIN)
     */
    public function addAction($id, $type, $pos = null, $bone = null, $side = null)
    {
        $history = array('type' => $type);
        if (null !== $pos) {
            $history['pos'] = $pos;
        }

        switch ($type) {
            case self::DEAL: {
                $history['bone'] = $bone;
            }
                break;
            case self::THROWIN: {
                $history['bone'] = $bone;
                $history['side'] = $side;
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
     * @param int              $id   Порядковый номер комманды обновления игры с которого необходимо отображать анимацию
     * @param Core_Game_Domino $game Объект игры
     * @param int|null         $pos  Позиция игрока за игровым столом, для которого необходимо отображать анимацию
     *
     * @return string
     */
    public function show($id, Core_Game_Domino $game, $pos = null)
    {
        //Проверка наличия истории анимации
        if (!count($this->_history)) {
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
        //Проверка начального состояния игрового стола, когда есть только первая анимация - раздача игральных костей
        if (count($this->_history) <= 1) {
            //Получаем текущие данные игрового стола в виде XML без данных игральных костей на руках игроков (т.к. они есть в текущей анимации)
            $staticData = $game->saveXml($pos, true);
        } elseif ($id <= 0 || ($this->getLastCommand() - $id) > 4) {
            //Пользователь вошел в процессе игры либо пропустил более четырех обновлений - отдаем текущие статические данные стола
            return $game->saveXml($pos);
        }

        //Формирование данных игрового стола вместе с анимацией
        return $staticData . $this->saveXml($id, $pos);
    }

    /**
     * Вывод в ответе сервера флага старта игры
     * (выводится только при смене статуса игры с WAIT на PLAY, т.е. когда отрисовывается первая анимация раздачи костей)
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
     * @param null $pos Позиция игрока за игровым столом, для которого необходимо отображать анимацию
     *
     * @return string
     */
    public function saveXml($id, $pos = null)
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
                    case self::DEAL: {
                        $xml->writeAttribute('pos', $history['pos']);
                        //Кость, если чужая, то закрытая
                        if (null === $pos || $history['pos'] == $pos) {
                            $xml->writeAttribute('bone', $history['bone']);
                        } else {
                            $xml->writeAttribute('bone', 'X');
                        }
                    }
                        break;
                    case self::THROWIN: {
                        $xml->writeAttribute('pos', $history['pos']);
                        $xml->writeAttribute('side', $history['side']);
                        $xml->writeAttribute('bone', $history['bone']);
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
