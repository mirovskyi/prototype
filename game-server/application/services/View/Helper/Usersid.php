<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 16.10.12
 * Time: 17:48
 *
 * Помошник вида, отображение ключа сессии пользователя
 */
class App_Service_View_Helper_Usersid extends Core_View_Helper_Abstract
{

    public function usersid($sid)
    {
        $currentSession = Core_Session::getInstance()->get(Core_Session::USER_NAMESPACE);
        if ($currentSession instanceof App_Model_Session_User) {
            if ($sid == $currentSession->getSid()) {
                return $currentSession->getKey();
            }
        }

        return $sid;
    }

}
