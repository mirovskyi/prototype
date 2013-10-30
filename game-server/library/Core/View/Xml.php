<?php

 
class Core_View_Xml extends Core_View
{

    const FILE_EXTENSION = 'xml.phtml';

    /**
     * Получение полного пути к файлу шаблона
     *
     * @param string|null $template
     * @return string|null
     */
    public function _getTemplatePath($template = null)
    {
        $path = parent::_getTemplatePath($template);
        if (!preg_match('/\.\w+$/', $template)) {
            $path .= '.' . static::FILE_EXTENSION;
        }

        return $path;
    }

}
