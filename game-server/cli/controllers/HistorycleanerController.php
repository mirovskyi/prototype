<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 30.08.12
 * Time: 11:58
 * To change this template use File | Settings | File Templates.
 */
class Cli_HistorycleanerController extends Core_Cli_Controller_Action
{

    public function clearAction()
    {
        //Объект сервиса очистки данных истории
        $service = new Cli_Service_Cleaner_History();
        //Удаление просроченных таблиц
        $service->dropExpiredTables();
    }

}
