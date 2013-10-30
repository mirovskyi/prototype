<?php

/**
 * Description of Position
 *
 * @author aleksey
 */
class Core_Game_Chess_Coords_Position 
{
    
    /**
     * Координаты шахматной доски
     */
    public static $_horizontal = array('A','B','C','D','E','F','G','H');
    public static $_vertical = array(1, 2, 3, 4, 5, 6, 7, 8);
    
    /**
     * Позиция по горизонтали (A-H)
     *
     * @var string 
     */
    protected $_x;
    
    /**
     * Позиция по вертикали (1-8)
     *
     * @var integer
     */
    protected $_y;
    
    
    /**
     * __construct
     *
     * @param string|array $position [Optional]
     */
    public function __construct($position = null)
    {
        if (is_string($position)) {
            $this->setPosition($position);
        } elseif (is_array($position) && count($position) == 2) {
            $this->setHorizontalIndex($position[0])
                 ->setVerticalIndex($position[1]);
        }
    }
    
    /**
     * Установка позиции по горизонтали (A-H)
     *
     * @param string $position
     * @return Core_Game_Chess_Coords_Position 
     */
    public function setHorizontalPosition($position)
    {
        if (!in_array($position, self::$_horizontal)) {
            throw new Core_Game_Chess_Exception('Unknown horizontal position given');
        }
        
        $this->_x = $position;
        return $this;
    }
    
    /**
     * Получение позиции по горизонтали (A-H)
     *
     * @return string 
     */
    public function getHorizontalPosition()
    {
        return $this->_x;
    }

    /**
     * Проверка наличия позиции по горизонтали
     *
     * @param string $position
     * @return bool
     */
    public static function hasHorizontalPosition($position)
    {
        return in_array($position, self::$_horizontal);
    }
    
    /**
     * Установка позиции по вертикали (1-8)
     *
     * @param integer $position
     * @return Core_Game_Chess_Coords_Position 
     */
    public function setVerticalPosition($position)
    {
        if (!in_array($position, self::$_vertical)) {
            throw new Core_Game_Chess_Exception('Unknown vertical position given');
        }

        $this->_y = $position;
        return $this;
    }
    
    /**
     * Получение позиции по вертикали (1-8)
     *
     * @return integer 
     */
    public function getVerticalPosition()
    {
        return $this->_y;
    }

    /**
     * Проверка наличия позиции по вертикали
     *
     * @param $position
     * @return bool
     */
    public static function hasVerticalPosition($position)
    {
        return in_array($position, self::$_vertical);
    }
    
    /**
     * Установка позиции
     *
     * @param string $position
     * @return Core_Game_Chess_Coords_Position 
     */
    public function setPosition($position)
    {
        $position = trim($position);
        if (!is_string($position) || strlen($position) != 2) {
            throw new Core_Game_Chess_Exception('Invalid piece position given');
        }
        
        $this->setHorizontalPosition($position[0])
             ->setVerticalPosition($position[1]);
        
        return $this;
    }
    
    /**
     * Получение позиции
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->getHorizontalPosition()
               . $this->getVerticalPosition();
    }
    
    /**
     * Установка горизонтальной позиции по индексу
     *
     * @param integer $index
     * @return Core_Game_Chess_Coords_Position 
     */
    public function setHorizontalIndex($index)
    {
        $horizontal = self::$_horizontal;
        if (!isset($horizontal[$index])) {
            throw new Core_Game_Chess_Exception('Unknown horizontal index ' . $index);
        }
        $this->setHorizontalPosition($horizontal[$index]);
        
        return $this;
    }
    
    /**
     * Получение индекса позиции по горизонтали
     *
     * @return integer 
     */
    public function getHorizontalIndex()
    {
        if (null !== $this->getHorizontalPosition()) {
            return array_search($this->getHorizontalPosition(), self::$_horizontal);
        }
        
        return null;
    }

    /**
     * Проверка наличия индекса позиции по горизонтали
     *
     * @param int $index
     * @return bool
     */
    public static function hasHorizontalIndex($index)
    {
        $horizontal = self::$_horizontal;
        return isset($horizontal[$index]);
    }
    
    /**
     * Установка вертикальной позиции по индексу
     *
     * @param integer $index
     * @return Core_Game_Chess_Coords_Position 
     */
    public function setVerticalIndex($index)
    {
        $vertical = self::$_vertical;
        if (!isset($vertical[$index])) {
            throw new Core_Game_Chess_Exception('Unknown vertical index ' . $index);
        }
        $this->setVerticalPosition($vertical[$index]);
        
        return $this;
    }
    
    /**
     * Получение индекса позиции по вертикали
     *
     * @return integer 
     */
    public function getVerticalIndex()
    {
        if (null !== $this->getVerticalPosition()) {
            return array_search($this->getVerticalPosition(), self::$_vertical);
        }
        
        return null;
    }

    /**
     * Проверка наличия индекса позиции по вертикали
     *
     * @param int $index
     * @return bool
     */
    public static function hasVerticalIndex($index)
    {
        $vertical = self::$_vertical;
        return isset($vertical[$index]);
    }

    /**
     * Смещение позиции на указанное кол-во клеток по горизонтали и вертикали
     *
     * @param int $x Смещение по горизонтали
     * @param int $y Смещение по вертикали
     * @return bool
     */
    public function shift($x = 0, $y = 0)
    {
        $isShift = false;

        if ($x != 0) {
            $x = $this->getHorizontalIndex() + $x;
            if ($this->hasHorizontalIndex($x)) {
                $this->setHorizontalIndex($x);
                $isShift = true;
            }
        }
        if ($y != 0) {
            $y = $this->getVerticalIndex() + $y;
            if ($this->hasVerticalIndex($y)) {
                $this->setVerticalIndex($y);
                $isShift = true;
            }
        }

        return $isShift;
    }

}