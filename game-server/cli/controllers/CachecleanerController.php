<?php

 
class Cli_CachecleanerController extends Core_Cli_Controller_Action
{

    public function serverAction()
    {
        $cachePath = APPLICATION_PATH . '/../data/cache/server/reflection.cache';
        exec('rm -f ' . $cachePath);

        if (!file_exists($cachePath)) {
            $result = 'Server cache successful cleared';
        } else {
            $result = 'Server cache clear fail';
        }

        $this->getResponse()->setBody($result);
    }

}
