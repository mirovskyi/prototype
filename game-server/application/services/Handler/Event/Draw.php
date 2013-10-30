<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 12.03.12
 * Time: 19:01
 *
 * Обработчик создания/подтверждения события предложения ничьи
 */
class App_Service_Handler_Event_Draw extends App_Service_Handler_Abstract
{

    /**
     * Обработка запроса
     *
     * @return string
     * @throws Exception
     */
    public function handle()
    {
        //Блокировка и обновление данных игры
        $this->getGameSession()->lockAndUpdate();

        try {
            //Запрос подтверждения/отказа от события увеличения ставки
            if (null !== $this->getRequest()->get('confirm')) {
                $this->_handleConfirm();
            }
            else {
                $this->_handleDraw();
            }
        } catch (Exception $e) {
            //Разблокировка данных игрового стола
            $this->getGameSession()->unlock();
            //Выбрасываем исключение
            throw $e;
        }

        //Сохранение и разблокировка данных игрового стола
        $this->getGameSession()->saveAndUnlock();

        //Возвращаем ответ сервера
        return $this->view->render();
    }

    /**
     * Проверка возможности предложения ничьи
     *
     * @param string $gameSid Идентификатор сессии игры
     * @param string $userSid Идентификатор сессии игрока
     *
     * @return bool
     */
    public static function canDrawOffer($gameSid, $userSid)
    {
        //Получаем список пользователей предлогавших ничью
        $key = $gameSid . ':draw';
        $drawOffers = Core_Storage::factory()->get($key);

        //Проверка наличия данных о предложении ничьи в игре
        if (!is_array($drawOffers)) {
            //Еще никто не предлогал ничью
            return true;
        }

        //Проверка наличия предложения ничьи от пользователя
        if (isset($drawOffers[$userSid])) {
            //Игрок уже предлогал ничью
            return false;
        } else {
            //Игрок еще не предлогал ничью
            return true;
        }
    }

    /**
     * Очищение данных предложения ничьи в игре
     *
     * @static
     *
     * @param string $gameSid Идентификатор сессии игры
     */
    public static function clear($gameSid)
    {
        //Удаление записей о предложении ничьи в игре
        $key = $gameSid . ':draw';
        Core_Storage::factory()->delete($key);
    }

    /**
     * Обработка подтверждения/отказа ничьи
     */
    protected function _handleConfirm()
    {
        if ($this->getRequest()->get('confirm')) {
            //Обработка события (подтверждение ничьи)
            $this->getGameSession()->getData()->handleEvent(App_Service_Events_Draw::name());
        } else {
            //Отказ от ничьи (завершение)
            $this->getGameSession()->getData()->getEvent(App_Service_Events_Draw::name())->destroy();
        }
    }

    /**
     * Обработка запроса создания события предложения ничьи
     *
     * @throws Core_Exception
     */
    protected function _handleDraw()
    {
        //Проверка возможности предложения ничьи у пользователя
        if (!$this->canDrawOffer($this->getGameSession()->getSid(), $this->getUserSession()->getSid())) {
            throw new Core_Exception('The player reached the limit of the draw offers count', 1513, Core_Exception::USER);
        }

        //Создание объекта события ничьи
        $event = new App_Service_Events_Draw();
        //Установка идентификатора сессии игрока, предложившего ничью
        $event->setDrawUser($this->getUserSession()->getSid());
        //Добавление события в игру
        $this->getGameSession()->getData()->addEvent($event);
        //Обработка события
        $this->getGameSession()->getData()->handleEvent($event->getName());

        //Запоминаем предложение ничьи пользователя
        $this->_saveDrawOffer();
    }

    /**
     * Сохранение факта предложения ничьи от пользователя
     */
    private function _saveDrawOffer()
    {
        //Объект работы с хранилищем
        $storage = Core_Storage::factory();

        //Лочим данные
        $key = $this->getGameSession()->getSid() . ':draw';
        $storage->lock($key);

        //Получаем список пользователей предлогавших ничью
        $drawOffers = $storage->get($key);
        /*if (!is_array($drawOffers)) {
            $drawOffers = array();
        }*/
        //Очищение данных о предыдущих предложениях ничьи
        //Каждый игрок за одину партию может предложить ничью один раз,
        //но если после него кто-то еще из игроков предложил ничью, у игрока снова появляется возможноть предложения
        $drawOffers = array();

        //Добавляем пользователя в список
        if (!isset($drawOffers[$this->getUserSession()->getSid()])) {
            $drawOffers[$this->getUserSession()->getSid()] = 1;
        } else {
            $drawOffers[$this->getUserSession()->getSid()] += 1;
        }

        //Сохраняем данные о предложении ничиьи
        $storage->set($key, $drawOffers);
        //Разблокируем данные
        $storage->unlock($key);
    }

}
