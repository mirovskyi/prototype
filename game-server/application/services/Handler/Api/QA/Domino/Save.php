<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 26.07.12
 * Time: 10:23
 * To change this template use File | Settings | File Templates.
 */
class App_Service_Handler_Api_QA_Domino_Save extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса. Возвращает ответ сервера
     *
     * @throws Core_Exception
     * @return string
     */
    public function handle()
    {
        //Получаем актуальные данные игры и блокируем сессию
        $this->getGameSession()->lockAndUpdate();
        //Проверка актуальности данных игры
        if ($this->_getGame()->getCommand() != $this->getRequest()->get('command')) {
            $this->getGameSession()->unlock();
            throw new Core_Exception('Game data is not actual');
        }

        //Инкремент команды обновления данных игры
        $this->_getGame()->incCommand();

        //Обновление данных резерва
        $reserveInfo = $this->getRequest()->get('reserve');
        $this->_updateReserve($reserveInfo);

        //Обновление данных ряда костей на игровом столе
        $seriesInfo = $this->getRequest()->get('series');
        $this->_updateSeries($seriesInfo);

        //Обновление данных игроков
        $playersInfo = $this->getRequest()->get('players');
        if (!is_array($playersInfo)) {
            $this->getGameSession()->unlock();
            throw new Core_Exception('Can\'t parse players data');
        }
        $this->_updatePlayers($playersInfo);

        //Добавляем в историю анимации флаг для перерисовки игрового стола
        $this->_getGame()->getAnimation()->addAction(
            $this->_getGame()->getCommand(),
            Core_Game_Durak_Animation::QA_UPDATE
        );

        //Сохраняем и разблокируем данные сессии игры
        $this->getGameSession()->saveAndUnlock();

        //Отдаем ответ сервера
        return $this->view->render();
    }

    /**
     * Обновление данных резерва в домино
     *
     * @param string $reserveInfo
     */
    private function _updateReserve($reserveInfo)
    {
        //Получение массива игральных костей
        $reserve = explode(',', $reserveInfo);
        //Очищаем данные костей
        $this->_getGame()->getReserve()->setBones(array());
        //Добавление полученных костей
        foreach($reserve as $bone) {
            if ($bone) {
                $this->_getGame()->getReserve()->addBone($bone);
            }
        }
    }

    /**
     * Обновление данных ряда игральных костей в домино
     *
     * @param string $seriesInfo
     */
    private function _updateSeries($seriesInfo)
    {
        //Получение массива игральных костей ряда
        $series = explode(',', $seriesInfo);
        //Очищаем текущие данные ряда
        $this->_getGame()->getSeries()->setBones(array());
        //Добавление игральных костей в ряд
        foreach($series as $bone) {
            if ($bone) {
                $this->_getGame()->getSeries()->addBone($bone);
            }
        }
    }

    /**
     * Обновление данных игроков за игроальным столом домино
     *
     * @param array $playersInfo
     */
    private function _updatePlayers($playersInfo)
    {
        //Обновление данных костей каждого игрока
        foreach($playersInfo as $info) {
            //Получаем объект игрока
            $player = $this->_getGame()->getPlayersContainer()->getPlayer($info['sid']);
            if (!$player) {
                continue;
            }
            //Обновляем информацию об остатке времени
            $player->setGametime($info['time']);
            //Обновляем данные карт
            if ($this->_getGame()->getStatus() == Core_Game_Abstract::STATUS_PLAY && isset($info['bones'])) {
                //Очищаем данные игральных костей игрока
                $player->getBoneArray()->setBones(array());
                //Добаляем по одной игральной кости
                foreach(explode(',', $info['bones']) as $bone) {
                    if ($bone) {
                        $player->getBoneArray()->addBone($bone);
                    }
                }
            }
        }
    }

    /**
     * Получение данных игры
     *
     * @return Core_Game_Domino
     */
    private function _getGame()
    {
        return $this->getGameSession()->getData();
    }
}
