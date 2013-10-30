<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 22.03.12
 * Time: 16:49
 *
 * Помошник вида, доступ к данным FlashMessenger
 */
class Addon_View_Helper_FlashMessenger extends Zend_View_Helper_Abstract
{

    /**
     * Объект помошника действий FlashMessanger
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $_flashMessenger;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->_flashMessenger = new Zend_Controller_Action_Helper_FlashMessenger();
    }

    /**
     * Получение сообщений
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_flashMessenger->getMessages();
    }

    /**
     * Проверка наличия сообщений
     *
     * @return bool
     */
    public function hasMessages()
    {
        return $this->_flashMessenger->hasMessages();
    }

    /**
     * Magic method __toString
     *
     * @return string
     */
    public function __toString()
    {
        $result = '';
        if ($this->hasMessages()) {
            foreach($this->getMessages() as $message) {
                $result .= $message . PHP_EOL;
            }
        }
        return $result;
    }

}
