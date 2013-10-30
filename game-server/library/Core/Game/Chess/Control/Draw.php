<?php
 
class Core_Game_Chess_Control_Draw extends Core_Game_Chess_Control_Abstract
{

    protected $_drawPiecesRelations = array(
        'K:K',
        'HK:K',
        'BK:K',
        'HHK:K',
        'HK:BK',
        'HK:HK',
        'BK:BK'
    );

    /**
     * Проверка ничьи
     *
     * @param bool $timeOff Флаг окончания времени партии у игрока
     * @param string|null $timeoutColor Цвет фигур пользователя, у которого закончилось время на партию
     *
     * @return bool
     */
    public function check($timeOff = false, $timeoutColor = null)
    {
        //Проверка наличия ничьи в случае окончания времени партии
        if ($timeOff) {
            return $this->_isTimeoutDraw($timeoutColor);
        }

        //Проверка троекратного повторения положения фигур на шахматной доске,
        //удалем из массива истории все повторяющиеся значения
        $history = array_unique($this->getBoard()->getHistory());
        //Получаем количество повторяющихся состояний шахматной доски
        $repeatCount = count($this->getBoard()->getHistory()) - count($history);
        //Проверка трех повторений
        if ($repeatCount >= 3) {
            return true;
        }

        //Обе стороны сделали 50 последних ходов без взятия и без хода пешкой
        if (count($this->getBoard()->getHistory()) == 100) {
            $draw = true;
            //Проход по всей истории перемещений
            $history = $this->getBoard()->getHistory();
            foreach($history as $key => $state) {
                //Проверка наличия следующего состояния шахматной доски
                if (isset($history[$key + 1])) {
                    //Проверка взятия либо перемещения пешки
                    if ($this->_checkPawnMove($state, $history[$key + 1])) {
                        $draw = false;
                        break;
                    }
                }
            }
            if ($draw) {
                return true;
            }
        }

        //Проверка наличия ничейной комбинации фигур на доске
        $arrStringPieces = $this->_piecesToStrings();
        return $this->_checkDrawRelation(
            $arrStringPieces[Core_Game_Chess_Piece_Abstract::WHITE],
            $arrStringPieces[Core_Game_Chess_Piece_Abstract::BLACK]
        );
    }

    /**
     * Проверка наличия ничьи по истечению времени партии у игрока
     *
     * @throws Core_Exception
     * @param int $timeoutColor Цвет фигур игрока, у которого истекло время
     * @return bool
     */
    protected function _isTimeoutDraw($timeoutColor)
    {
        //Формирование отсортировонной строки списка фигур игроков
        $arrStringPireces = $this->_piecesToStrings();

        //Определение игрока, у которого истекло время и его оппонента
        if ($timeoutColor == Core_Game_Chess_Piece_Abstract::WHITE) {
            $opponentPieces = $arrStringPireces[Core_Game_Chess_Piece_Abstract::BLACK];
        } else {
            $opponentPieces = $arrStringPireces[Core_Game_Chess_Piece_Abstract::WHITE];
        }

        //Если у оппонента только король - ничья
        if ($opponentPieces == 'K') {
            return true;
        }
        //Проверка всех возможных ситуаций ничьи (соотношений фигур противников)
        return $this->_checkDrawRelation(
            $arrStringPireces[Core_Game_Chess_Piece_Abstract::WHITE],
            $arrStringPireces[Core_Game_Chess_Piece_Abstract::BLACK]
        );
    }

    /**
     * Проверка перемещение или взятия пешки за один ход
     *
     * @param string $state1 Состоянеи шахматной доски перед совершением хода
     * @param string $state2 Состоянеи шахматной доски после совершения хода
     * @return bool
     */
    protected function _checkPawnMove($state1, $state2)
    {
        //Выборка всех пешек из строки состояния
        if (!preg_match_all('/P\w\d/',$state1, $matches1)) {
            //Пешек нет
            return true;
        }
        if (!preg_match_all('/P\w\d/',$state2, $matches2)) {
            //Пешек нет
            return true;
        }

        //Сравниваем количество пешек в двух состояниях шахматной доски,
        //если они не совпадают, значит было взятие пешки
        if (count($matches1[0]) != count($matches2[0])) {
            return true;
        }

        //Сравниваем расположения пешек
        if (implode('',$matches1[0]) != implode('', $matches2[0])) {
            return true;
        }

        //Не было ни хода пешкой, ни взятие пешки
        return false;
    }

    /**
     * Проверка наличия ничейной комбинации фигур
     *
     * @param string $strPieces1 Строка фигур игрока 1
     * @param string $strPieces2 Строка фигур игрока 2
     *
     * @return bool
     */
    private function _checkDrawRelation($strPieces1, $strPieces2)
    {
        //Проход по всем возможным ничейным комбинациям фигур
        foreach($this->_drawPiecesRelations as $relation) {
            $relation = explode(':', $relation);
            if ($strPieces1 == $relation[0] && $strPieces2 == $relation[1]) {
                return true;
            }
            if ($strPieces2 == $relation[0] && $strPieces1 == $relation[1]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Поучение массива ряда фигур оппонентов в виде строк
     *
     * @return string
     */
    private function _piecesToStrings()
    {
        //Получаем массивы наименований фигур белых и черных
        $arrWhiteName = array();
        $arrBlackName = array();
        foreach($this->getBoard()->getPieces() as $line) {
            foreach($line as $piece) {
                if ($piece->getColor() == Core_Game_Chess_Piece_Abstract::WHITE) {
                    $arrBlackName[] = $piece->getName();
                } else {
                    $arrWhiteName[] = $piece->getName();
                }
            }
        }
        //Формирование отсортировонной строки списка фигур игроков
        sort($arrWhiteName, SORT_STRING);
        sort($arrBlackName, SORT_STRING);
        //Возвращаем массив строк с данными фигур
        return array(
            Core_Game_Chess_Piece_Abstract::WHITE => implode('', $arrWhiteName),
            Core_Game_Chess_Piece_Abstract::BLACK => implode('', $arrBlackName)
        );
    }

}
