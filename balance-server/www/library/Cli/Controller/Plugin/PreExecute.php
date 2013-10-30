<?php

/**
 * Description of PreExecute
 *
 * @author aleksey
 */
class Cli_Controller_Plugin_PreExecute extends Zend_Controller_Plugin_Abstract 
{
    
    protected $_systemControllers = array(
        'error', 'info', 'top'
    );
    
    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    {
        //Получаем действие
        $action = $request->getActionName();
        //Проверка запущенного скрипта действия
        $count = $this->_processCount('-a ' . $action);

        if ($count > 1) {
            //Скрипт запущен
            //Zend_Registry::get('log')->info('SCRIPT ACTION ' . $action . ' has allready executed');
            die();
        }
        //Проверка перехода на системный контроллер
        /*if (in_array($request->getControllerName(), $this->_systemControllers)) {
            return false;
        }
        //Получаем наименование скрипта
        $script = $request->getModuleName()
                  . ':' . $request->getControllerName()
                  . ':' . $request->getActionName();
        //Получаем данные последнего запуска скрипта
        $process = new Cli_Model_Process();
        $process->fetchRow('script = "' . $script . '"', 'id DESC');
        if ($process->getId() != null) {
            //Проверка статуса процесса
            if (Cli_Model_Process::isExecute($process->getPid())) {
                //Предыдущий процесс еще не отработал, проверяем флаг принудительного завершения
                if ($request->hasParam('k')) {
                    //Убиваем предыдущий процесс
                    $this->_killProcess($process);
                } else {
                    //Завершаем работу данного процесса
                    Zend_Registry::get('log')->warn('Script ' . $process->getScript()
                                                    . ' has allready run with PID: '
                                                    . $process->getPid());
                    exit;
                }
            }
        }
        //Запись данных о запуске процесса
        $process->setScript($script)
                ->setPid(posix_getpid())
                ->setExecuteTime(date('Y-m-d H:i:s'))
                ->setLastStatus('ERR')
                ->save();  */
    }
    
    /**
     * Метод принудительного завершения процесса
     * @param Cli_Model_Process $process 
     */
    protected function _killProcess(Cli_Model_Process $process)
    {
        Zend_Registry::get('log')->info('Kill process ' . $process->getPid()
                                        . ' of script ' . $process->getScript());
        $result = exec('kill ' . $process->getPid());
        if ($result != '') {
            Zend_Registry::get('log')->info('Can not kill process ' 
                                            . $process->getPid() . ' of script'
                                            . $process->getScript()
                                            . ', message: ' . $result);
            //Завершаем текущий процесс
            exit;
        }
    }

    /**
     * Получение списка запущенных скриптов
     *
     * @param string $script
     *
     * @return int
     */
    private function _processCount($script)
    {
        //Получение списка скриптов
        exec('ps -ax|grep ' . $script, $output);
        //Убираем записи с grep
        $count = 0;
        foreach($output as $line) {
            if (!strstr($line, 'grep')) {
                $count++;
            }
        }
        //Возвращаем количество запущенных скриптов
        return $count;
    }
    
}