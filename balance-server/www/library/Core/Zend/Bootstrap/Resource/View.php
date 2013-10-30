<?php

/**
 * Description of View
 *
 * @author aleksey
 */
class Core_Zend_Bootstrap_Resource_View
    extends Zend_Application_Resource_ResourceAbstract 
{
    
    public function init()
    {
        $options = $this->getOptions();
        $view = new Addon_Smarty_View($options);
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
                                                                'ViewRenderer');
        if (isset($options['doctype'])) {
            $view->getHelper('doctype')->doctype($options['doctype']);
        }
        if (isset($options['contentType'])) {
            $view->getHelper('headMeta')->appendHttpEquiv(
                                        'Content-Type', $options['contentType']);
        }
        if (isset($options['title'])) {
            $view->getHelper('headTitle')->headTitle($options['title']);
        }
        $viewRenderer->setViewSuffix('tpl');
        $viewRenderer->setView($view);
        return $view;
    }
    
}