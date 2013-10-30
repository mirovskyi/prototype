<?php

/**
 * Description of PostExecute
 *
 * @author aleksey
 */
class Cli_Controller_Plugin_PostExecute extends Zend_Controller_Plugin_Abstract 
{
    
    public function postDispatch(Zend_Controller_Request_Abstract $request) 
    {
        //PID текущего процесса
        /*$pid = posix_getpid();
        //Получаем объект процесса из БД
        $process = new Cli_Model_Process();
        $process->fetchRow('pid = ' . $pid);
        if ($process->getId() != null) {
            //Изменяем время завершения скрипта
            $process->setTerminateTime(date('Y-m-d H:i:s'));
            //Проверка наличия исключения в объекте ответа
            if (!$this->getResponse()->isException()) {
                //Скрипт отработал корректно, изменяем время успешного запуска и завершения
                $process->setSuccessExecuteTime($process->getExecuteTime())
                        ->setSuccessTerminateTime($process->getTerminateTime())
                        ->setLastStatus('SUC');
            }
            //Обновление данных процесса в БД
            $process->save();
        }   */
    }
    
}