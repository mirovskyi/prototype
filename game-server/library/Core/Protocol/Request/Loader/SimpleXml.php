<?php


class Core_Protocol_Request_Loader_SimpleXml extends Core_Protocol_Request_Loader_Abstract
{

    /**
     * Объект XML документа
     *
     * @var SimpleXMLElement
     */
    protected $_xml;


    /**
     * __construct
     *
     * @param string|null $data
     */
    public function __construct($data = null)
    {
        parent::__construct($data);

        if (null === $this->getRulesDirectory()) {
            $this->setRulesDirectory(realpath(dirname(__FILE__)) . '/rules');
        }
    }

    /**
     * Обработка данных запроса
     *
     * @throws Core_Protocol_Exception
     * @return void
     */
    public function load()
    {
        //Получаем объект SimpleXMLElement из данных запроса
        try {
            $xml = new SimpleXMLElement($this->_data);
        } catch (Core_Protocol_Exception $e) {
            $this->setFault(new Core_Protocol_Fault(630));
            return;

        }

        //Проверка наличия имени вызываемого метода
        if (!isset($xml->methodCall) ||
                !isset($xml->methodCall->attributes()->name)) {
            $this->setFault(new Core_Protocol_Fault(632));
            return;
        }
        $methodName = (string) $xml->methodCall->attributes()->name;

        //Определение пространства имен (имя обработчика) и название вызываемого метода
        if (strstr($methodName, '.')) {
            $temp = explode('.', $methodName);
            $this->setNamespace($temp[0])
                 ->setMethod($temp[1]);
            unset($temp);
        } else {
            $this->setMethod($methodName);
        }

        //Проверка наличия файла правил обработки
        $filename = $this->getRulesDirectory() . '/' . $this->getNamespace()
                    . '/' . $this->getMethod() . '.php';
        $filename = str_replace('//', '/', $filename);
        if (!file_exists($filename)) {
            throw new Core_Protocol_Exception('Parse rules file was not find in ' . $filename);
        }

        //Получаем правила обработки
        $rules = include $filename;
        if (!is_array($rules)) {
            throw new Core_Protocol_Exception('Invalid parse rules format');
        }

        //Обработка данных запроса
        $this->_xml = $xml;
        $this->_extractParams($rules);
    }

    /**
     * Преобразование данных запроса в массив параметров
     *
     * @param array $rules
     * @return void
     */
    protected function _extractParams($rules)
    {
        foreach($rules as $param => $rule) {
            //Получение значения параметра
            switch ($rule['type']) {
                case 'array': $value = $this->_xmlToArray($rule['xml']);
                    break;
                default : $value = $this->_xmlToValue($rule['type'], $rule['xml']);
            }
            //Проверка наличия параметра в запросе
            if (null === $value && isset($rule['required']) && true == $rule['required']) {
                //В запросе нет обязательного параметра
                //throw new Core_Protocol_Exception('Not all required parameters are received', 638, Core_Exception::USER);
                $this->setFault(new Core_Protocol_Fault(638, '', Core_Exception::USER));
            }
            //Проверка валидности значения
            if (null !== $value && isset($rule['validators'])) {
                //Если тип значения массив достаем валидаторы для отдельных ключей массива
                if ($rule['type'] == 'array') {
                    foreach($value as $key => $val) {
                        if (isset($rule['validators'][$key])) {
                            if (!$this->_validate($val, $rule['validators'][$key])) {
                                break;
                            }
                        }
                    }
                } else { //Применяем валидатор для значения параметра
                    if (!$this->_validate($value, $rule['validators'])) {
                        break;
                    }
                }
            }
            //Добавляем параметр запроса
            if (null !== $value) {
                $this->addParam($param, $value);
            }
        }
    }

    /**
     * Преобразование XML элемента в значение
     *
     *
     * @param string $type
     * @param array  $rules
     *
     * @throws Core_Protocol_Exception
     * @return bool|string
     */
    private function _xmlToValue($type, $rules)
    {
        if (!isset($rules['xpath'])) {
            throw new Core_Protocol_Exception('Invalid XML request parse rules');
        }

        $result = $this->_xml->xpath($rules['xpath']);
        if (!$result) {
            return null;
        }

        if (isset($rules['fromAttribute'])) {
            $value = (string) $result[0][$rules['fromAttribute']];
        } else {
            $value = (string) $result[0];
        }

        switch ($type) {
            case 'int':
            case 'integer': return intval($value);
            case 'float': return floatval($value);
            case 'bool':
            case 'boolean': {
                if (strtolower($value) == 'true') {
                    return true;
                } elseif (strtolower($value) == 'false') {
                    return false;
                } else {
                    return (bool) $value;
                }
            }
            default: return $value;
        }
    }

    /**
     * Преобразование XML элемента в массив
     *
     *
     * @param array $rules
     *
     * @throws Core_Protocol_Exception
     * @return array|bool
     */
    private function _xmlToArray($rules)
    {
        if (!isset($rules['xpath'])) {
            throw new Core_Protocol_Exception('Invalid XML request parse rules');
        }

        $result = $this->_xml->xpath($rules['xpath']);
        if (!$result) {
            return null;
        }

        $arrResult = array();

        if (isset($rules['keyElem']) && isset($rules['valElem'])) {
            foreach($result as $element) {
                $key = (string) $element->{$rules['keyElem']};
                $value = (string) $element->{$rules['valElem']};
                $arrResult[$key] = $value;
            }
        } elseif (isset($rules['attributes'])) {
            //Получение массива атрибутов
            $attributes = $result[0]->attributes();
            foreach($attributes as $key => $val) {
                if (is_array($rules['attributes']) && !in_array($key, $rules['attributes'])) {
                    continue;
                }
                $arrResult[$key] = (string) $val;
            }
        } else {
            if (count($result) > 1) {
                foreach($result as $element) {
                    $arrResult[] = $this->_parseArray($element);
                }
            } else {
                $arrResult = $this->_parseArray($result[0]);
            }
        }

        return $arrResult;
    }

    /**
     * Преобразование SimpleXMLElement в массив
     *
     * @param SimpleXMLElement $element
     * @return array
     */
    private function _parseArray(SimpleXMLElement $element)
    {
        $arrResult = array();
        if ($element->count() > 0) {
            $value = array();
            foreach($element->children() as $node) {
                $name = $node->getName();
                if (isset($value[$name])) {
                    if (!is_array($value[$name])) {
                        $value[$name] = (array)$value[$name];
                    }
                    elseif (!isset($value[$name][0])) {
                        $value[$name] = array($value[$name]);
                    }
                    array_push($value[$name], $this->_parseArray($node));
                } else {
                    $value[$name] =  $this->_parseArray($node);
                }
            }
        } else {
            $value = (string) $element;
        }
        return $arrResult[$element->getName()] = $value;
    }

    /**
     * Проверка валидности значения
     *
     * @param mixed $value      Значение для проверки валидности
     * @param array $validators Массив данных валидаторов
     * @return bool
     */
    private function _validate($value, array $validators)
    {
        //Проход по всем валидаторам
        foreach($validators as $key => $val) {
            if (is_string($val)) {
                $validator = $this->_getValidator($val);
            } elseif (is_array($val)) {
                $validator = $this->_getValidator($key, $val);
            } else {
                $validator = $val;
            }

            //Проверка объекта валидатора
            if (!$validator instanceof Core_Validate_Interface) {
                //Некорректный объект валидатора
                $this->setFault(new Core_Protocol_Fault(1001));
                return false;
            }

            //Проверка валидности значения
            if (!$validator->valid($value)) {
                //Значение не валидно
                $this->setFault(
                    new Core_Protocol_Fault(
                        $validator->getErrorCode(),
                        $validator->getErrorMessage(),
                        Core_Exception::USER
                    )
                );
                return false;
            }
        }

        return true;
    }

}