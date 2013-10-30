<?php

/**
 * Класс протокольной ошибки
 *
 * @author aleksey
 */
class Core_Protocol_Fault 
{
    
    /**
     * Код ошибки
     *
     * @var integer
     */
    protected $_code;
    
    /**
     * Кодировка ответа об ошибке
     *
     * @var string 
     */
    protected $_encoding = 'UTF-8';
    
    /**
     * Сообщение об ошибке
     *
     * @var string 
     */
    protected $_message;
    
    /**
     * Тип ошибки
     *
     * @var string
     */
    protected $_type;
    
    /**
     * Список возможных ошибок
     *
     * @var array
     */
    public static $_internal = array(
        404 => 'Unknown Error',
        500 => 'Server Internal Error',
        
        //100 - 150 auth errors
        101 => 'Ann error has occurred while saving user session',
        102 => 'Invalid request data to create a user session',
        103 => 'User session was not found',
        104 => 'Game session was not found',
        105 => 'Chat session was not found',
        106 => 'The player created another session. Current session has deleted',
        110 => 'Unknown service',
        111 => 'Lock session spoofing',
        
        //201 - 210 general game errors
        201 => 'Unknown game',
        202 => 'Ann error has occurred while saving game session',
        203 => 'Ann error occurred while storage game session',
        204 => 'Ann error has occurred while join user to the game session',
        205 => 'Wrong order of data update',
        206 => 'User does not exists in the list of invitees to a private gaming table',
        207 => 'You can\'t invite the opponents for a public gaming table',
        208 => 'Invited user is already involved in the game',
        209 => 'Permission denied to edit game settings',
        210 => 'Game observe is deny',
        211 => 'Failed to surrender. Game does not active.',
        212 => 'Game position is allready kept',
        213 => 'Game not finished',
        214 => 'Game closed',
        215 => 'The game is already in place',

        //270 - 300 chat errors
        270 => 'Chat session was not found',
        271 => 'Ann error has occurred while saving chat session',
        
        //301 - ? room errors
        301 => 'User session was not found in room',
        302 => 'Game session was not found in room',
        303 => 'Game session has allready busy',
        304 => 'Failed to create game. User balance should be more than game bet',
        305 => 'Failed to restart game. User balance should be more than game bet',
        306 => 'User\'s account balance should be more than the minimum balance specified in the settings of the game',
        307 => 'Zero bet amount',
        308 => 'Game session has closed',
        309 => 'Inconsistency to play',
        320 => 'Ann error occurred while saving room data',

        //501 - Lobby functionality exceptions
        501 => 'Not enough money to make gift',

        // 610 - 619 reflection errors
        610 => 'Invalid method class',
        611 => 'Unable to attach function or callback; not callable',
        612 => 'Unable to load array; not an array',
        613 => 'One or more method records are corrupt or otherwise unusable',

        // 620 - 629 dispatch errors
        620 => 'Method does not exist',
        621 => 'Error instantiating class to invoke method',
        622 => 'Method missing implementation',
        623 => 'Calling parameters do not match signature',

        // 630 - 639 request errors
        630 => 'Unable to read request',
        631 => 'Failed to parse request',
        632 => 'Invalid request, no method passed; request must contain a \'methodName\' tag',
        633 => 'Param must contain a value',
        634 => 'Invalid method name',
        635 => 'Invalid XML provided to request',
        636 => 'Error creating xmlrpc value',
        637 => 'Invalid request params',
        638 => 'Not all required parameters are received',

        // 640 - 649 system.* errors
        640 => 'Method does not exist',

        // 650 - 659 response errors
        650 => 'Invalid XML provided for response',
        651 => 'Failed to parse response',
        652 => 'Invalid response',
        653 => 'Invalid XMLRPC value in response',

        //700 API errors
        700 => 'Failed to get user\'s account balance',
        701 => 'Failed to charge user balance',

        //1000 Validator errors
        1000 => 'Unknown validator',
        1001 => 'Invalid validator object',
        1002 => 'Value does not appear to be an integer',

        //1500 Events errors
        1500 => 'Previous event has not processed yet',
        1501 => 'Event does not exists',
        1510 => 'Amount of the proposed bet must exceed the current',
        1511 => 'The amount of the increased bet can not exceed the minimum balance of players',
        1513 => 'The player reached the limit of the draw offers count',
        
        //2001 - * response errors Filler Game
        2001 => 'Received color is already used by the current user',
        2002 => 'Received color is already used by opponent',

        //2051 - * response errors Chess Game
        2051 => 'No piece for the given position',
        2052 => 'Invalid move',
        2053 => 'Try to move opponent piece',
        2054 => 'The movement of the piece is forbidden',
        2055 => 'Invalid piece move, check for own king',
        2056 => 'Unknown chess piece name given',

        //3001 - * response errors Durak Game
        3001 => 'Reached limit the number of cards in the hand',
        3002 => 'Can\'t go to the partner',
        3003 => 'Invalid card format',
        3004 => 'Failed to compare cards of different suits',
        3005 => 'This card is not in the process',
        3006 => 'Card is allready beaten off',
        3007 => 'Beating card must be upper',
        3008 => 'The player has no cards for the current drawing',
        3009 => 'Defender player can\'t do refuse action',
        3010 => 'Refuse failed. Player is not in the game',
        3011 => 'Refuse failed. Player is attacker',
        3012 => 'Player is not defender',
        3013 => 'Player has not card',
        3014 => 'Player can\'t transfer cards',
        3015 => 'Defender can\'t throw card',
        3016 => 'Player is not attacker',

        //3050 - * game history errors
        3050 => 'The service of game history is not available',
        3051 => 'Maximum records count of favorite games has exceeded',
        3052 => 'Record not found',
        3053 => 'Ann error occured while save history record',
        3054 => 'Ann error occured while delete history record',

        //4000 - * shop operation error
        4000 => 'insufficient user balance for buy item',
        4001 => 'Item does not exists',
        4002 => 'User allready has the item',
        4003 => 'Can not update status transaction to \'SUC\'',

        //5000 - Лобби платформы igrok
        5000 => 'Error writing to database',
        5001 => 'Duplicate login',
        5002 => 'Authentication failed',
        5003 => 'Failed to find user record',
        5004 => 'Failed to create user session',


    );
    
    
    /**
     * __construct
     *
     * @param integer $code
     * @param string $message
     * @param string $type 
     */
    public function __construct($code = 500, $message = '', $type = Core_Exception::SYSTEM)
    {
        $this->setCode($code);
        $code = $this->getCode();

        if (empty($message) && isset(self::$_internal[$code])) {
            $message = self::$_internal[$code];
        } elseif (empty($message)) {
            $message = 'Internal Server Error';
        }

        $this->setMessage($message)
             ->setType($type);
    }
    
    /**
     * Установка кода ошибки
     *
     * @param integer $code
     * @return Core_Protocol_Fault 
     */
    public function setCode($code)
    {
        $this->_code = (int) $code;
        return $this;
    }
    
    /**
     * Получение кода ошибки
     *
     * @return integer 
     */
    public function getCode()
    {
        return $this->_code;
    }
        
    /**
     * Установка сообщения об ошибке
     *
     * @param string $message
     * @return Core_Protocol_Fault 
     */
    public function setMessage($message)
    {
        $this->_message = (string) $message;
        return $this;
    }
    
    /**
     * Получение сообщения об ошибке
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * Установка типа ошибки
     *
     * @param string $type
     * @return Core_Protocol_Fault 
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }
    
    /**
     * Получение типа ошибки
     *
     * @return string 
     */
    public function getType()
    {
        return $this->_type;
    }
    
    /**
     * Установка кодировки ответа с ошибкой
     *
     * @param string $encoding
     * @return Core_Protocol_Fault 
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }
    
    /**
     * Получение кодировки ответа с ошибкой
     *
     * @return Core_Protocol_Fault 
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }
    
    /**
     * Проверка, является ли запрос оповещением об ошибке
     *
     * @param string $data
     * @return bool 
     */
    public static function isFault($data)
    {
        $fault = new self();
        try {
            $isFault = $fault->load($data);
        } catch(Core_Protocol_Exception $e) {
            return false;
        }
        
        return $isFault;
    }

    /**
     * Загрузка данных запроса об ошибке
     *
     * @param string $data
     * @return bool
     */
    public function load($data)
    {
        $xml = new SimpleXMLElement($data);
        if (isset($xml->error) &&
                isset($xml->error->code) &&
                isset($xml->error->message)) {
            $this->setCode((string)$xml->error->code);
            $this->setMessage((string)$xml->error->message);
            if (isset($xml->error->type)) {
                $this->setType((string)$xml->error->type);
            }
            return true;
        }
    }
    
    /**
     * Получение контента ошибки
     *
     * @return string
     */
    public function save()
    {
        //Генератор XML документа
        $generator = Core_Xml::generator($this->getEncoding());
        //Формирование XLM ошибки
        $generator->openElement('response');
        $generator->openElement('error');
        $generator->openElement('code', $this->getCode())
                  ->closeElement('code');
        $generator->openElement('message', $this->getMessage())
                  ->closeElement('message');
        $generator->openElement('type', $this->getType())
                  ->closeElement('type');
        $generator->closeElement('error');
        $generator->closeElement('response');

        //Возвращаем тело XML документа
        return $generator->flush();
    }
    
    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->save();
    }
}