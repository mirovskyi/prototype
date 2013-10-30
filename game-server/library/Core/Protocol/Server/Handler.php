<?php

 
class Core_Protocol_Server_Handler
{

    /**
     * Объект запроса
     *
     * @var Core_Protocol_Request
     */
    protected $_request;

    /**
     * Объект ответа
     *
     * @var Core_Protocol_Response
     */
    protected $_response;


    /**
     * __construct
     *
     * @param Core_Protocol_Request|null $request
     * @param Core_Protocol_Response|null $response
     */
    public function __construct(Core_Protocol_Request $request = null, Core_Protocol_Response $response = null)
    {
        if (null !== $request) {
            $this->setRequest($request);
        }
        if (null !== $response) {
            $this->setResponse($response);
        }

        $this->init();
    }

    /**
     * Инициализация обработчика
     *
     * @return void
     */
    public function init()
    {}

    /**
     * Установка объекта запроса
     *
     * @param Core_Protocol_Request $request
     * @return Core_Protocol_Server_Handler
     */
    public function setRequest(Core_Protocol_Request $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Получение объекта запроса
     *
     * @return Core_Protocol_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Установка объекта ответа
     *
     * @param Core_Protocol_Response $response
     * @return Core_Protocol_Server_Handler
     */
    public function setResponse(Core_Protocol_Response $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Получение объекта ответа
     *
     * @return Core_Protocol_Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Действия перед дтспетчеризацией
     *
     * @return void
     */
    public function preDispatch()
    {}

    /**
     * Действия после диспетчиризации
     *
     * @return void
     */
    public function postDispatch()
    {}

    /**
     * Диспетчеризация
     *
     * @throws Core_Server_Exception
     * @param string $action
     * @return void
     */
    public function dispatch($action)
    {
        $this->preDispatch();
        if (!method_exists($this, $action)) {
            throw new Core_Server_Exception(sprintf('Method %s does not exists', $action));
        }
        $this->$action();
        $this->postDispatch();
    }

    /**
     * Запуск обработчика
     *
     * @param Core_Protocol_Request|null $request
     * @param Core_Protocol_Response|null $response
     * @return Core_Protocol_Response
     */
    public function run(Core_Protocol_Request $request = null, Core_Protocol_Response $response = null)
    {
        if (null !== $request) {
            $this->setRequest($request);
        }
        if (null !== $response) {
            $this->setResponse($response);
        }

        if (!($method = $this->getRequest()->getMethod())) {
            $method = 'index';
        }

        $this->dispatch($method);
        return $this->getResponse();
    }

}
