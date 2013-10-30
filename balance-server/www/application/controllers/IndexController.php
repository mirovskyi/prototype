<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
    }

    public function indexAction()
    {
        echo '<pre>';
        print_r(Zend_Registry::get('serverconfig')->get('application'));die;
    }

}
