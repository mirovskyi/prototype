<?php
 
abstract class Core_Cli_Controller_Action
{

    /**
     * Объект запроса
     *
     * @var Core_Cli_Request
     */
    protected $_request;

    /**
     * Объект ответа
     *
     * @var Core_Cli_Response
     */
    protected $_response;


    /**
     * Создание нового контроллера
     *
     * @param Core_Cli_Request $request
     * @param Core_Cli_Response $response
     */
    public function __construct(Core_Cli_Request $request, Core_Cli_Response $response = null)
    {
        $this->_request = $request;

        if (null !== $response) {
            $this->_response = $response;
        } else {
            $this->_response = new Core_Cli_Response();
        }

        $this->init();
    }

    /**
     * Инициализаия контроллера
     *
     * @return void
     */
    public function init()
    {}

    /**
     * Получение объекта запроса
     *
     * @return Core_Cli_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Получение объекта ответа
     *
     * @return Core_Cli_Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Прлучение списка параметров запроса
     *
     * @return array
     */
    protected function _getAllParams()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * Получение значения параметра запроса
     *
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    protected function _getParam($name, $default = null)
    {
        return $this->getRequest()->getParam($name, $default);
    }

    /**
     * Проверка наличия параметра в запросе
     *
     * @param string $name
     * @return bool
     */
    protected function _hasParam($name)
    {
        return $this->getRequest()->has($name);
    }

}