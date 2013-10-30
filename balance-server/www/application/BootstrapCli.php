<?php

class BootstrapCli extends Zend_Application_Bootstrap_Bootstrap
{

    public function _initFront()
    {
        $front = $this->getPluginResource('frontcontroller');
        $front->getFrontController()
            ->setRequest('Cli_Controller_Request_Cli')
            ->setResponse('Zend_Controller_Response_Cli')
            ->setRouter('Cli_Controller_Router_Cli');
    }

    public function _initView()
    {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'ViewRenderer'
        );
        $viewRenderer->setNeverRender();
    }

}
