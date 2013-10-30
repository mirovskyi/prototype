<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 10.07.12
 * Time: 12:31
 *
 * Обработчик сохранения изменений в данных игры (для тестирования)
 */
class App_Service_Handler_Api_QA_Durak_Save extends App_Service_Handler_Abstract
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

        //Обновление данных колоды
        $packInfo = $this->getRequest()->get('pack');
        $this->_updatePack($packInfo);

        //Обновление данных отбоя
        $pulldownInfo = $this->getRequest()->get('pulldown');
        $this->_updatePulldown($pulldownInfo);

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
     * Обновление данных карточной колоды
     *
     * @param string $packInfo
     */
    private function _updatePack($packInfo)
    {
        if ($packInfo) {
            //Очищаем данные колоды
            $this->_getGame()->getPack()->setCards(array());
            //Добаляем по одной карте в колоду
            foreach(explode(',', $packInfo) as $card) {
                $this->_getGame()->getPack()->add($card);
            }
        }
    }

    /**
     * Обновление данных отбоя
     *
     * @param string $pulldownInfo
     */
    private function _updatePulldown($pulldownInfo)
    {
        if ($pulldownInfo) {
            //Очищаем данные отбоя
            $this->_getGame()->getPulldown()->setCards(array());
            //Добаляем по одной карте в отбой
            foreach(explode(',', $pulldownInfo) as $card) {
                $this->_getGame()->getPulldown()->add($card);
            }
        }
    }

    /**
     * Обновление данных игроков
     *
     * @param array $playersInfo
     */
    private function _updatePlayers(array $playersInfo)
    {
        foreach($playersInfo as $info) {
            //Получаем объект игрока
            $player = $this->_getGame()->getPlayersContainer()->getPlayer($info['sid']);
            if (!$player) {
                continue;
            }
            //Обновляем информацию об остатке времени
            $player->setGametime($info['time']);
            //Обновляем данные карт
            if ($this->_getGame()->getStatus() == Core_Game_Abstract::STATUS_PLAY && isset($info['cards'])) {
                //Очищаем данные карт пользователя
                $player->getCardArray()->setCards(array());
                //Добаляем по одной карте пользователю
                foreach(explode(',', $info['cards']) as $card) {
                    $player->getCardArray()->add($card);
                }
                //Проверка наличия у пользователя карт (установка статуса в игре)
                if (count($player->getCardArray())) {
                    $player->setPlay();
                } else {
                    $player->setPlay(false);
                }
            }
        }
    }

    /**
     * Получение данных игры
     *
     * @return Core_Game_Durak
     */
    private function _getGame()
    {
        return $this->getGameSession()->getData();
    }
}
