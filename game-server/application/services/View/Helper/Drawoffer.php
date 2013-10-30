<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 31.10.12
 * Time: 9:35
 *
 * Помошник вида. Формирование элемента флага возможности предложения ничьи
 */
class App_Service_View_Helper_Drawoffer extends Core_View_Helper_Abstract
{

    /**
     * Выполнение действий помошника вида. Отображение флага возможности предложить ничью в партии.
     *
     * @param string $gameSid Идентификатор сессии игры
     * @param string $userSid Идентификатор сессии игрока
     *
     * @return string
     */
    public function drawoffer($gameSid, $userSid)
    {
        //Получаем флаг возможности предложить ничью у пользователя
        $offer = intval(App_Service_Handler_Event_Draw::canDrawOffer($gameSid, $userSid));

        //Формирование XML
        $xml = new XMLWriter();
        $xml->openMemory();

        $xml->startElement('draw');
        $xml->writeAttribute('offer', (string)$offer);
        $xml->endElement();

        //Отдаем XML
        return $xml->flush(false);
    }

}
