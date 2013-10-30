<?php

/**
 * Description of Class
 *
 * @author aleksey
 */
class Console_Model_Generate_Orm_Class extends Console_Model_Generate_Orm_Abstract 
{

    /**
     * Генерация кода класса модели таблицы
     * @param string $className Имя класса
     * @return boolean
     */
    public function generate($className) 
    {
        //Установка имени класса
        $this->_className = $className;
        //Генерация кода класса
        try {
            $class = new Zend_CodeGenerator_Php_Class(array(
                'docblock' => array(
                    'shortDescription' => 'ORM модель таблицы ' . $this->getDbTable()->info('name'),
                ),
                'name' => $this->_className,
                'properties' => $this->_properties(),
                'methods' => $this->_methods()
            ));
        } catch (Zend_CodeGenerator_Exception $e) {
            return false;
        }
        //Установка класса в PHP файл
        $this->getPHPFile()->setClass($class);
        return true;
    }
    
    /** 
     * Метод получения списка полей класса
     * @return array
     */
    protected function _properties()
    {
        //своиства класса
        $properties = array();
        //Поля таблицы
        foreach($this->getDbTable()->info('metadata') as $column) {
            $docblock = new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Поле таблицы, ' . $column['COLUMN_NAME'],
                'tags' => array(
                    array(
                        'name' => 'var',
                        'description' => $this->_typeOf($column['DATA_TYPE'])
                    )
                )
            ));
            $properties[] = array(
                'name' => '_' . $this->_camelFieldName($column['COLUMN_NAME']),
                'visibility' => 'protected',
                'docblock' => $docblock
            );
        }
        //Mapper
        $docblock = new Zend_CodeGenerator_Php_Docblock(array(
            'shortDescription' => 'Объект модели доступа к данным таблицы',
            'tags' => array(
                array(
                    'name' => 'var',
                    'description' => $this->_mapperClassName()
                )
            )
        ));
        $properties[] = array(
            'name' => '_mapper',
            'visibility' => 'protected',
            'docblock' => $docblock
        );
        return $properties;
    }
    
    /**
     * Метод получения списка сгенерированных методов класса
     * @return array
     */
    protected function _methods()
    {
        //Список методов класса
        $methods = array();
        //Генерация конструктора
        $methods[] = $this->_construct();
        //Генерация метода setOptions
        $methods[] = $this->_options();
        //Генерация метода __get
        $methods[] = $this->_magic_get();
        //Генерация метода __set
        $methods[] = $this->_magic_set();
        //Генерация setters, getters методов 
        $methods = array_merge($methods, 
                               $this->_setter_getter_methods(),
                               $this->_mapper_methods());
        //Генерация метода select
        $methods[] = $this->_select();
        //Генерация метода find
        $methods[] = $this->_find();
        //Генерация метода save
        $methods[] = $this->_save();
        //Генерация метода fetchRow
        $methods[] = $this->_fetch_row();
        //Генерация метода fetchAll
        $methods[] = $this->_fetch_all();
        //Генерация метода delete
        $methods[] = $this->_delete();
        return $methods;
    }
    
    /**
     * Генерация конструктора
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _construct()
    {
        //Генерация конструктора
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => '__construct',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'options',
                        'datatype' => 'array'
                    ))
                )
            )),
            'name' => '__construct',
            'parameters' => array(array(
                'name' => 'options',
                'defaultValue' => null
            )),
            'body' => '$this->setOptions($options);' . PHP_EOL
        ));
    }
    
    /**
     * Генерация метода setOptions
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _options()
    {
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод установки параметров модели',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'options',
                        'datatype' => 'array'
                    )),
                )
            )),
            'name' => 'setOptions',
            'parameters' => array(array('name' => 'options')),
            'body' => 'if (is_array($options) && count($options) > 0) {' . PHP_EOL
                      . '    foreach($options as $name => $value) {' . PHP_EOL
                      . '        $this->__set($name, $value);' . PHP_EOL
                      . '    }' . PHP_EOL
                      . '}'
        ));
    }
    
    /**
     * Генерация метода __get
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _magic_get()
    {
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Магический метод __get',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'name',
                        'datatype' => 'string'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => 'mixed'
                    )),
                )
            )),
            'name' => '__get',
            'parameters' => array(array('name' => 'name')),
            'body' => '$method = \'get\' . ucfirst($name);' . PHP_EOL
                      . 'if (method_exists($this, $method)) {' . PHP_EOL
                      . '    return $this->$method();' . PHP_EOL
                      . '} else {' . PHP_EOL
                      . '    throw new Exception(\'Unknown method \' . $method . \' called in \' . get_class($this));'
                      . PHP_EOL . '}'
        ));
    }
    
    /**
     * Генерация метода __set
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _magic_set()
    {
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Магический метод __set',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'name',
                        'datatype' => 'string'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'value',
                        'datatype' => 'mixed'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => 'mixed'
                    )),
                )
            )),
            'name' => '__set',
            'parameters' => array(
                array('name' => 'name'),
                array('name' => 'value')
            ),
            'body' => '$method = \'set\' . ucfirst($name);' . PHP_EOL
                      . 'if (method_exists($this, $method)) {' . PHP_EOL
                      . '    $this->$method($value);' . PHP_EOL
                      . '}'
        ));
    }
    
    /**
     * Генерация setter и getter методов
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _setter_getter_methods()
    {
        $methods = array();
        foreach($this->getDbTable()->info('metadata') as $column) {
            //Имя поля класса
            $fieldName = $this->_camelFieldName($column['COLUMN_NAME']);
            //setter
            $methods[] = new Zend_CodeGenerator_Php_Method(array(
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'shortDescription' => 'Метод установки значения поля ' . $column['COLUMN_NAME'],
                    'tags' => array(
                        new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                            'paramName' => $fieldName,
                            'datatype' => $this->_typeOf($column['DATA_TYPE'])
                        )),
                        new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                            'datatype' => $this->_className
                        )),
                    )
                )),
                'name' => 'set' . ucfirst($fieldName),
                'parameters' => array(array('name' => $fieldName)),
                'body' => '$this->_' . $fieldName. ' = $' . $fieldName . ';' . PHP_EOL
                          . 'return $this;'
            ));
            //getter
            $methods[] = new Zend_CodeGenerator_Php_Method(array(
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'shortDescription' => 'Метод получения значения поля ' . $column['COLUMN_NAME'],
                    'tags' => array(
                        new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                            'datatype' => $this->_typeOf($column['DATA_TYPE'])
                        )),
                    )
                )),
                'name' => 'get' . ucfirst($fieldName),
                'body' => 'return $this->_' . $fieldName . ';'
            ));
        }
        return $methods;
    }
    
    /**
     * Генерация методов setMapper, getMapper
     * @return array
     */
    public function _mapper_methods()
    {
        $mapperClass = $this->_mapperClassName();
        $methods = array(
            new Zend_CodeGenerator_Php_Method(array(
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'shortDescription' => 'Метод установки объекта модели доступа к данным таблицы',
                    'tags' => array(
                        new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                            'paramName' => 'mapper',
                            'datatype' => $mapperClass
                        )),
                        new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                            'datatype' => $this->_className
                        ))
                    )
                )),
                'name' => 'setMapper',
                'parameters' => array(
                    array(
                        'name' => 'mapper',
                        'type' => $mapperClass
                    )
                ),
                'body' => '$this->_mapper = $mapper;' . PHP_EOL
                          . 'return $this;'
            )),
            new Zend_CodeGenerator_Php_Method(array(
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'shortDescription' => 'Метод получения объекта модели доступа к данным таблицы',
                    'tags' => array(
                        new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                            'datatype' => $mapperClass
                        )),
                    )
                )),
                'name' => 'getMapper',
                'body' => 'if (!$this->_mapper instanceof ' . $mapperClass . ') {' . PHP_EOL
                          . '    $this->setMapper(new ' . $mapperClass . '());' . PHP_EOL
                          . '}' . PHP_EOL
                          . 'return $this->_mapper;'
            ))
        );
        return $methods;
    }

    /**
     * Генерация метода select
     * @return Zend_CodeGenerator_Php_Method
     */
    protected function _select()
    {
        //Генерация метода select
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Получение объекта выборки',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => 'Zend_Db_Select'
                    )),
                )
            )),
            'name' => 'select',
            'body' => 'return $this->getMapper()->select();'
        ));
    }

    /**
     * Генерация метода find
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _find()
    {
        //Название поля с первичным ключем
        $primary = $this->getDbTable()->info('primary');
        //Генерация метода find
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод поиска данных записи по первичному ключу ('
                                      . $primary[1] . ')',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'id',
                        'datatype' => 'integer|string'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => $this->_className
                    )),
                )
            )),
            'name' => 'find',
            'parameters' => array(array('name' => 'id')),
            'body' => '$this->getMapper()->find($this, $id);' . PHP_EOL
                      . 'return $this;'
        ));
    }
    
    /**
     * Генерация метода save
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _save()
    {
        //Генерация метода save
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод сохранения объекта в БД',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => 'integer|boolean'
                    )),
                )
            )),
            'name' => 'save',
            'body' => 'return $this->getMapper()->save($this);'
        ));
    }
    
    /**
     * Генерация метода fetchRow
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _fetch_row()
    {
        //Генерация метода fetchRow
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод поиска и получения одной записи из БД',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'where',
                        'datatype' => 'string|array|Zend_Db_Table_Select',
                        'description' => 'Условие запроса'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'order',
                        'datatype' => 'string|array',
                        'description' => 'Условие сортировки'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => $this->_className
                    )),
                )
            )),
            'name' => 'fetchRow',
            'parameters' => array(
                array(
                    'name' => 'where',
                    'defaultValue' => null
                ),
                array(
                    'name' => 'order',
                    'defaultValue' => null
                )
            ),
            'body' => '$this->getMapper()->fetchRow($this, $where, $order);' . PHP_EOL
                      . 'return $this;'
        ));
    }
    
    /**
     * Генерация метода fetchAll
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _fetch_all()
    {
        //Генерация метода fetchRow
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод поиска и получения записей из БД',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'where',
                        'datatype' => 'string|array|Zend_Db_Table_Select',
                        'description' => 'Условие запроса'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'order',
                        'datatype' => 'string|array',
                        'description' => 'Условие сортировки'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'count',
                        'datatype' => 'integer',
                        'description' => 'Количество записей в результате'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'offset',
                        'datatype' => 'integer',
                        'description' => 'Номер записи, с которой ведется поиск'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => $this->_className . '[]'
                    )),
                )
            )),
            'name' => 'fetchAll',
            'parameters' => array(
                array(
                    'name' => 'where',
                    'defaultValue' => null
                ),
                array(
                    'name' => 'order',
                    'defaultValue' => null
                ),
                array(
                    'name' => 'count',
                    'defaultValue' => null
                ),
                array(
                    'name' => 'offset',
                    'defaultValue' => null
                )
            ),
            'body' => 'return $this->getMapper()->fetchAll($where, $order, $count, $offset);'
        ));
    }
    
    /**
     * Генерация метода delete
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _delete()
    {
        //Генерация метода delete
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Удаление записи из БД',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'where',
                        'datatype' => 'string|array',
                        'description' => 'Условие выборки записей для удаления'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => 'boolean'
                    )),
                )
            )),
            'name' => 'delete',
            'parameters' => array(array('name' => 'where')),
            'body' => 'return $this->getMapper()->delete($where);'
        ));
    }
}