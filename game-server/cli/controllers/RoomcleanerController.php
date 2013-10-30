<?php
 
class Cli_RoomcleanerController extends Core_Cli_Controller_Action
{

    public function clearAction()
    {
        Zend_Registry::get('log')->debug('START CLEAR');
        $cleaner = new Cli_Service_Cleaner_Room('filler');
        $cleaner->clear();
        $cleaner = new Cli_Service_Cleaner_Room('filler_sota');
        $cleaner->clear();
        $cleaner = new Cli_Service_Cleaner_Room('chess');
        $cleaner->clear();
        $cleaner = new Cli_Service_Cleaner_Room('checkers');
        $cleaner->clear();
        $cleaner = new Cli_Service_Cleaner_Room('durak');
        $cleaner->clear();
        $cleaner = new Cli_Service_Cleaner_Room('domino');
        $cleaner->clear();
        $cleaner = new Cli_Service_Cleaner_Room('backgammon');
        $cleaner->clear();
        $cleaner = new Cli_Service_Cleaner_Room('durak_transfer');
        $cleaner->clear();
        Zend_Registry::get('log')->debug('END CLEAR');
    }

}
