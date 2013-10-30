<?php

/**
 * Перехват запроса получения фотографии пользователя.
 *
 * @author aleksey
 */
class Core_Plugin_UserPhoto extends Core_Plugin_Abstract 
{
    
    /**
     * Фото по умолчанию
     */
    const DEFAULT_PHOTO = 'question_c.gif';
    
    /**
     * Получеие ссылки фотографии пользователя
     *
     * @param string $key Ключ значения URL фотографии в хранилище
     * @return string 
     */
    public static function getPhotoUrl($key)
    {
        return 'http://' . $_SERVER['HTTP_HOST']
               . $_SERVER['SCRIPT_NAME']
               . '?/images/' . $key;
    }
    
    /**
     * Pre-handler
     */
    public function preHandle() 
    {
        //Данные запроса
        $query = $_SERVER['QUERY_STRING'];

        //Проверка запроса фотографии пользователя
        if (strstr($query, '/images/')) {
            //Получаем ключ записи с URL фотографией
            $key = str_replace('/images/', '', $query);
            //Получаем значения по ключу из хранилища
            $photoUrl = Core_Storage::factory()->get($key);
            //Проверка наличия URL фото
            if (!$photoUrl) {
                //Установка URL фото по умоланию
                $photoUrl = $this->_getDefaultPhotoUrl();
            }

            //Отдаем контент фото
            $this->_sendPhoto($photoUrl);
        }
    }
    
    /**
     * Установка заголовка
     *
     * @param string $photoUrl 
     */
    protected function _sendPhoto($photoUrl = null)
    {
        //Адрес изображения
        if (null === $photoUrl) {
            $photoUrl = $this->_getDefaultPhotoUrl();
        }
        
        //Получаем тип изображения
        $dotPos = strrpos($photoUrl, '.');
        $extension = substr($photoUrl, $dotPos + 1);
        
        //Определение типа контента в заголовке
        switch ($extension) {
            case 'gif': $ctype = 'image/gif'; break;
            case 'png': $ctype = 'image/png'; break;
            case 'jpeg':
            case 'jpg': $ctype = 'image/jpg'; break;
            default: $ctype = 'image/' . $extension; break;
        }
        
        //Заголовок
        header("Content-Type: $ctype");
        //Контент изображения
        echo @file_get_contents($photoUrl);
        die;
    }
    
    /**
     * Получение адреса файла с фото по умолчанию
     *
     * @return string 
     */
    private function _getDefaultPhotoUrl()
    {
        return APPLICATION_PATH . '/../public/images/' . self::DEFAULT_PHOTO;
    }
    
}