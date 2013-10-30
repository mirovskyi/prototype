<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 18.05.12
 * Time: 13:34
 * To change this template use File | Settings | File Templates.
 */
class CliController extends Cli_Controller_Action
{

    public function indexAction()
    {
        echo 'CLI' . PHP_EOL;
    }

    public function errorAction()
    {
        echo 'ERROR' . PHP_EOL;
    }

    public function onlineAction()
    {
        //Модель обновления количества online игроков на игровых серверах
        $onlineModel = new App_Service_Cli_UserOnline();
        //Обновление данных
        $onlineModel->updateOnline();
    }

}
