<?php

/**
 * Description of ClassMapper
 *
 * @author aleksey
 */
class Console_Model_Generate_Orm_ClassMapper extends Console_Model_Generate_Orm_Abstract 
{
    
    /**
     * Метод генерации кода класса мапера
     * @param string $className
     * @return boolean 
     */
    public function generate($className) 
    {
        //Установка названия класса модели сущности
        $this->_className = $className;
        //Создание класса
        try {
            $class = new Zend_CodeGenerator_Php_Class(array(
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'shortDescription' => 'Класс реализующий слой доступа к данным БД в ORM модели' .
                                          ', для таблицы ' . $this->getDbTable()->info('name')
                )),
                'name' => $this->_mapperClassName(),
                'properties' => $this->_properties(),
                'methods' => $this->_methods()
            ));
        } catch (Zend_CodeGenerator_Exception $e) {
            return false;
        }
        //Установка кода класса в PHP файл
        $this->getPHPFile()->setClass($class);
        return true;
    }
    
    /**
     * Метод создания массива полей класса
     * @return array 
     */
    protected function _properties()
    {
        return array(
            new Zend_CodeGenerator_Php_Property(array(
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'shortDescription' => 'Объект модели таблицы',
                    'tags' => array(
                        array(
                            'name' => 'var',
                            'description' => 'Zend_Db_Table_Abstract'
                        )
                    )
                )),
                'name' => '_dbTable',
                'visibility' => 'protected'
            ))
        );
    }
    
    /**
     * Метод создания списка методов класса
     * @return array
     */
    protected function _methods()
    {
        //Генерация методов
        return array(
            $this->_set_table(),
            $this->_get_table(),
            $this->_save(),
            $this->_select(),
            $this->_find(),
            $this->_fetch_row(),
            $this->_fetch_all(),
            $this->_delete()
        );
    }
    
    /**
     * Генерация кода метода setDbTable
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _set_table()
    {
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод установки объекта модели таблицы',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'dbTable',
                        'datatype' => 'string|Zend_Db_Table_Abstract',
                        'description' => 'Объект модели таблицы ИЛИ имя класса модели таблицы'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => $this->_mapperClassName()
                    ))
                )
            )),
            'name' => 'setDbTable',
            'parameters' => array(array('name' => 'dbTable')),
            'body' => 'if ($dbTable instanceof Zend_Db_Table_Abstract) {' . PHP_EOL
                      . '    $this->_dbTable = $dbTable;' . PHP_EOL
                      . '} elseif (is_string($dbTable)) {' . PHP_EOL
                      . '    if (class_exists($dbTable)) {' . PHP_EOL
                      . '        $this->_dbTable = new $dbTable();' . PHP_EOL
                      . '    } else {' . PHP_EOL
                      . '        throw new Exception(\'Class \' . $dbTable . \' does not exist\');' . PHP_EOL
                      . '    }' . PHP_EOL
                      . '}' . PHP_EOL
                      . 'return $this;'
        ));
    }
    
    /**
     * Генерация кода метода getDbTable
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _get_table()
    {
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод получения объекта модели таблицы',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => 'Zend_Db_Table_Abstract'
                    ))
                )
            )),
            'name' => 'getDbTable',
            'body' => 'if (!$this->_dbTable instanceof Zend_Db_Table_Abstract) {' . PHP_EOL
                      . '    $this->setDbTable(\'' . $this->_dbClassName() . '\');' . PHP_EOL
                      . '}' . PHP_EOL
                      . 'return $this->_dbTable;'
        ));
    }
    
    /**
     * Генерация кода метода save()
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _save()
    {
        $body = '$data = array();' . PHP_EOL;
        foreach($this->getDbTable()->info('cols') as $column) {
            $getMethod = 'get' . ucfirst($this->_camelFieldName($column));
            $body .= 'if ($entity->' . $getMethod . '() !== null) {' . PHP_EOL
                     . '    $data[\'' . $column . '\'] = $entity->' . $getMethod . '();' . PHP_EOL
                    . '}' . PHP_EOL;
        }
        $primary = $this->getDbTable()->info('primary');
        $body .= 'if (!isset($data[\'' . $primary[1] . '\'])) {' . PHP_EOL
                 . '    $id = $this->getDbTable()->insert($data);' . PHP_EOL
                 . '    if ($id != null) {' . PHP_EOL
                 . '        $entity->set' . ucfirst($this->_camelFieldName($primary[1])) . '($id);' . PHP_EOL
                 . '    }' . PHP_EOL
                 . '    return $id;' . PHP_EOL
                 . '} else {' . PHP_EOL
                 . '    return $this->getDbTable()->update($data, array(\'' . $primary[1] . ' = \' . '
                 . '$entity->get' . ucfirst($this->_camelFieldName($primary[1])) . '()));' . PHP_EOL
                 . '}';
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод сохранения объекта в БД',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'entity',
                        'datatype' => $this->_className,
                        'description' => 'Объект сущности'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => 'integer|boolean'
                    ))
                )
            )),
            'name' => 'save',
            'parameters' => array(
                array(
                    'name' => 'entity',
                    'type' => $this->_className
                )
            ),
            'body' => $body
        ));
    }

    /**
     * Генерация кода метода select()
     * @return Zend_CodeGenerator_Php_Method
     */
     protected function _select()
     {
         return new Zend_CodeGenerator_Php_Method(array(
             'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                 'shortDescription' => 'Метод получения объекта выборки',
                 'tags' => array(
                     new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                         'datatype' => 'Zend_Db_Select'
                     ))
                 )
             )),
             'name' => 'select',
             'body' => 'return $this->getDbTable()->select();'
         ));
     }
    
    /**
     * Генерация кода метода find()
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _find()
    {
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод поиска записи по первичному ключу',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'entity',
                        'datatype' => $this->_className,
                        'description' => 'Объект сущности'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'id',
                        'datatype' => 'integer|string',
                        'description' => 'Значение первичного ключа'
                    ))
                )
            )),
            'name' => 'find',
            'parameters' => array(
                array(
                    'name' => 'entity',
                    'type' => $this->_className
                ),
                array('name' => 'id')
            ),
            'body' => '$result = $this->getDbTable()->find($id);' . PHP_EOL
                      . 'if ($result->count() > 0) {' . PHP_EOL
                      . '    $row = $result->current();' . PHP_EOL
                      . '} else {' . PHP_EOL
                      . '    return;' . PHP_EOL
                      . '}' . PHP_EOL
                      . $this->_setDataBodyBlock()
        ));
    }
    
    /**
     * Генерация кода метода fetchRow()
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _fetch_row()
    {
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод поиска записи в БД',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'entity',
                        'datatype' => $this->_className,
                        'description' => 'Объект сущности'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'where',
                        'datatype' => 'string|array|Zend_Db_Table_Select',
                        'description' => 'Условия поиска'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'order',
                        'datatype' => 'string|array',
                        'description' => 'Условия сортировки'
                    ))
                )
            )),
            'name' => 'fetchRow',
            'parameters' => array(
                array(
                    'name' => 'entity',
                    'type' => $this->_className
                ),
                array(
                    'name' => 'where',
                    'defaultValue' => null
                ),
                array(
                    'name' => 'order',
                    'defaultValue' => null
                )
            ),
            'body' => '$result = $this->getDbTable()->fetchRow($where, $order);' . PHP_EOL
                      . 'if (count($result) > 0) {' . PHP_EOL
                      . '    $row = $result;' . PHP_EOL
                      . '} else {' . PHP_EOL
                      . '    return;' . PHP_EOL
                      . '}' . PHP_EOL
                      . $this->_setDataBodyBlock()
        ));
    }
    
    /**
     * Генерация кода метода fetchAll()
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _fetch_all()
    {
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод выборки записей в БД по заданному условию',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'where',
                        'datatype' => 'string|array|Zend_Db_Table_Select',
                        'description' => 'Условия выборки'
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
            'body' => '$entities = array();' . PHP_EOL
                      . '$result = $this->getDbTable()->fetchAll($where, $order, $count, $offset);' . PHP_EOL
                      . 'if ($result->count() > 0) {' . PHP_EOL
                      . '    foreach($result as $row) {' . PHP_EOL
                      . '        $entity = new ' . $this->_className . '();' . PHP_EOL
                      . $this->_setDataBodyBlock('        ')
                      . '        $entity->setMapper($this);' . PHP_EOL
                      . '        $entities[] = $entity;' . PHP_EOL
                      . '    }' . PHP_EOL
                      . '}' . PHP_EOL
                      . 'return $entities;'
        ));
    }
    
    /**
     * Генерация кода метода delete()
     * @return Zend_CodeGenerator_Php_Method 
     */
    protected function _delete()
    {
        return new Zend_CodeGenerator_Php_Method(array(
            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Метод удаления записей по указанному условию выборки',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramName' => 'where',
                        'datatype' => 'string|array',
                        'description' => 'Условия выборки записей для удаления'
                    )),
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => 'integer'
                    ))
                )
            )),
            'name' => 'delete',
            'parameters' => array(array('name' => 'where')),
            'body' => 'return $this->getDbTable()->delete($where);'
        ));
    }
    
    protected function _setDataBodyBlock($spaces = '')
    {
        $body = '';
        foreach($this->getDbTable()->info('cols') as $column) {
            $body .= $spaces . '$entity->set' . ucfirst($this->_camelFieldName($column)) 
                     . '($row->' . $column . ');' . PHP_EOL;
        }
        return $body;
    }
    
}