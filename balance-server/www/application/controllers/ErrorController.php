<?php

class ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $error_handler = $this->getRequest()->getParam('error_handler');

        if ($error_handler) {
            $this->view->assign('exception', $error_handler->exception);
        }

    }

}
