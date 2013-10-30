<?php

class SocialpayController extends Zend_Controller_Action
{

    /**
     * Инициализация обработки платежа для mail.ru
     *
     * @throws Zend_Exception
     */
    public function mailruAction()
    {
        //Настройка контекста действий
        $this->_helper->contextSwitch()->addActionContext('mailru', 'json')
                                       ->initContext('json');

        Zend_Registry::get('log')->info('REQUEST DATA:'.PHP_EOL.'REQUEST_URI: '.$_SERVER['REQUEST_URI'].
                                                        PHP_EOL.print_r($this->getRequest()->getParams(), true));

        try {
            //Получаем обьект обработки платежа
            $notification = new App_Service_Server_Payment_Notification('mailru');

            //Обработка платежа
            if ($notification->success($this->getRequest()->getParams())) {
                $this->view->assign(array('status' => 1));
            } else {
                throw new Zend_Exception('Could not get the payment model');
            }
        } catch (Exception $e) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('Error handling transaction: ' . $e);
            }

            $errCode = ($e->getCode()) ? $e->getCode() : 700;
            //Eckb код ошибки = 2 (ошибка временная), то сервис будет слать повторные запросы
            $critical = ($errCode == 2) ? false : true;

            if (!$critical) {
                $this->view->assign(array('status' => 0, 'error_code' => $errCode));
            } else {
                $this->view->assign(array('status' => 2, 'error_code' => $errCode));
            }
        }
    }

    /**
     * Инициализация обработки платежа для odnoklassniki.ru
     *
     * @throws Zend_Exception
     */
    public function odnoklassnikiAction()
    {
        //Настройка контекста действий
        $this->_helper->contextSwitch()->addActionContext('odnoklassniki', 'xml')
                                       ->initContext('xml');

        Zend_Registry::get('log')->info('REQUEST DATA:'.PHP_EOL.'REQUEST_URI: '.$_SERVER['REQUEST_URI'].
                                                        PHP_EOL.print_r($this->getRequest()->getParams(), true));

        try {
            //Получаем обьект обработки платежа
            $notification = new App_Service_Server_Payment_Notification('odnoklassniki');

            //Обработка платежа
            if ($notification->success($this->getRequest()->getParams())) {
                $this->view->assign('error' , false);
            } else {
                throw new Zend_Exception('Could not get the payment model');
            }
        } catch (Exception $e) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('Error handling transaction: ' . $e);
            }

            $errCode = ($e->getCode()) ? $e->getCode() : 1;
            //Eckb код ошибки = 2, ошибка временная, и сервис будет слать повторные запросы
            $critical = ($errCode == 2) ? false : true;

            if (!$critical) {
                $this->view->assign('error' , array('code' => 2, 'msg' => $e->getMessage()));
            } else {
                $this->getResponse()->setHeader('invocation-error', '1');
                $this->view->assign('error' , array('code' => 1, 'msg' => $e->getMessage()));
            }
        }
    }

    /**
     * Инициализация обработки платежа для vk.com
     *
     * @throws Zend_Exception
     */
    public function vkontakteAction()
    {
        //Настройка контекста действий
        $this->_helper->contextSwitch()->addActionContext('vkontakte', 'json')
                                       ->initContext('json');

        Zend_Registry::get('log')->info('REQUEST DATA:'.PHP_EOL.'REQUEST_URI: '.$_SERVER['REQUEST_URI'].
                                                        PHP_EOL.print_r($this->getRequest()->getParams(), true));

        try {
            //Получаем обьект обработки платежа
            $notification = new App_Service_Server_Payment_Notification('vkontakte');

            //Получаем тип уведомления
            $action = $this->getRequest()->getParam('notification_type');
            //Определяем режим обработки
            $mode = (substr($action, -4) == 'test') ? '_test' : '';

            //Инициализация покупки
            if ($action == 'get_item'.$mode) {
                $response = $notification->init($this->getRequest()->getParams());
                $this->view->assign('response', $response);
            } elseif ($action == 'order_status_change'.$mode) {
                //Обработка платежа
                if ($notification->success($this->getRequest()->getParams())) {
                    $this->view->assign('response', array(  'order_id' => $this->getRequest()->getParam('order_id'),
                                                            'app_order_id' => $this->getRequest()->getParam('item_id')
                                                        ));
                } else {
                    throw new Zend_Exception('Could not get the payment model');
                }

            }
        } catch (Exception $e) {
            if (Zend_Registry::getInstance()->isRegistered('log')) {
                Zend_Registry::get('log')->err('Error handling transaction: ' . $e);
            }

            $errCode = ($e->getCode()) ? $e->getCode() : 1;
            //Eckb код ошибки = 2, ошибка временная, и сервис будет слать повторные запросы
            $critical = ($errCode == 2) ? false : true;

            $this->view->assign('error', array( 'err_code' => $errCode,
                                                'err_msg' => $e->getMessage(),
                                                'critical' => $critical
                                              ));
        }
    }

}
