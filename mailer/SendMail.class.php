<?php
/**
 * Класс для отправки писем
 * 
 * Date: 22.11.12
 * 
 * @author      Andrey Avol Volynkin
 * @version     0.1.0
 * @copyright   Copyright (c) 2012, Avol
 * @licence L-M18
 */
class SendMail
{
    /**
     * Массив получателей
     * @var array
     */
    private static $_recipients = array();
    /**
     * Отправитель
     * @var array
     */
    private static $_from = array( ); // 'www@glavhost.com', 'Robot'
    /**
     * Адрес для ответа
     * @var array
     */
    private static $_replyTo = array();
    /**
     * Адрес сервера для отправки через сокет
     * @var string
     */
    private static $_socketServer = '';
    /**
     * Порт на сервера для отправки через сокет
     * @var int
     */
    private static $_socketPort = 25;
    /**
     * Логин для отправки через сокет
     * @var string
     */
    private static $_socketLogin = '';
    /**
     * Пароль для отправки через сокет
     * @var string
     */
    private static $_socketPassword = '';
    /**
     * Дополнительные флаги командной строки sendmail
     * @var string
     */
    private static $_additionalParameters = '';
    /**
     * Дополнительные заголовки
     * @var array
     */
    private static $_additionalHeaders = array();

    /**
     * Добавление нового получателя
     * 
     * @param string $email Адрес e-mail
     * @param string $name Имя
     */
    public static function addRecipient( $email, $name = '' )
    {
        self::$_recipients[] = self::_addEmail( $email, $name );
    }

    /**
     * Отчистка списка получателей
     */
    public static function clearRecipients()
    {
        self::$_recipients = array();
    }

    /**
     * Установить адрес отправителя
     * 
     * @param string $email Адрес e-mail
     * @param string $name Имя
     */
    public static function setFrom( $email, $name = '' )
    {
        self::$_from = self::_addEmail( $email, $name );
    }

    /**
     * Установить адрес для ответа
     * 
     * @param string $email Адрес e-mail
     * @param string $name Имя
     */
    public static function setReplyTo( $email, $name = '' )
    {
        self::$_replyTo = self::_addEmail( $email, $name );
    }

    /**
     * Установить адрес сервера и порт для отправки посредством сокетов
     * 
     * @param string $server Адрес сервера SMTP
     * @param int $port Используемый порт на сервере (по-умолчанию 25)
     * @param string $login Логин, если нужен
     * @param string $password Пароль, если нужен
     */
    public static function useSocketMail( $server, $port = 25, $login = '', $password = '' )
    {
        self::$_socketServer = $server;
        self::$_socketPort = $port;
        self::$_socketLogin = $login;
        self::$_socketPassword = $password;
    }

    /**
     * Установить дополнительные флаги командной строки sendmail
     * 
     * @param string $parameters Строка параметров
     */
    public static function setAdditionalParameters( $parameters )
    {
        self::$_additionalParameters = $parameters;
    }

    /**
     * Установить дополнительные заголовки
     * 
     * @param array $headers Массив заголовков (ключ => значение)
     */
    public static function setAdditionalHeaders( $headers )
    {
        self::$_additionalHeaders = $headers;
    }

    /**
     * Отправить письмо
     * 
     * @param string $subject Тема писбма
     * @param string $message Текст сообщения
     *
     * @return bool Успешность отправки
     */
    public static function send( $subject, $message )
    {
        if ( !empty( self::$_socketServer ) )
        {
            return self::_sendSocket( $subject, $message );
        }
        else
        {
            return self::_sendInternal( $subject, $message );
        }
    }

    /**
     * Общий метод добавления нового email
     * 
     * @param string $email Адрес e-mail
     * @param string $name Имя
     *
     * @return array|bool
     */
    private static function _addEmail( $email, $name = '' )
    {
        $email_parts = self::_parseEmailString( $email );
        
        if ( !$email_parts )
        {
            return array();
        }
        
        if ( !empty( $name ) )
        {
            $email_parts[1] = $name;
        }
        
        return $email_parts;
    }

    /**
     * Разбор строка с адресом email и возможным именем.
     * Возвращает массив с адресом в первом элементе
     * и именем во втором.
     * При отсутствии имени, используется первая часть адреса.
     * 
     * @param string $email Строка с адресом
     *
     * @return array|bool Адрес и имя
     */
    private static function _parseEmailString( $email )
    {
        if ( preg_match( '/(?:^|^(.*?)\s*<)([\w!#$%&\'*=?+^`{|}~.-]+)@([\w.-]+)>?$/', $email, $matches ) )
        {
            if ( empty( $matches[1] ) )
            {
                $name = $matches[2];
            }
            else
            {
                $name = $matches[1];
            }
            
            return array(
                    $matches[2] . '@' . $matches[3],
                    $name
                );
        }
        
        return false;
    }

    /**
     * Кодирует строку в кодировке UTF-8 в Base64
     * 
     * @param string $string Исходная строка
     *
     * @return string Кодированная строка
     */
    private static function _getEncodedString( $string )
    {
        return '=?UTF-8?B?' . base64_encode( $string ) . '?=';
    }

    /**
     * Подготавливает адрес к виду, пригодному для использования в заголовках Email
     * 
     * @param array $address Массив с адресом [адрес, имя]
     *
     * @return bool|string Готовая строка или false
     */
    private static function _getEmailAddress( $address )
    {
        if ( empty( $address ) || !is_array( $address )
            || !isset( $address[0] ) || !isset( $address[1] ) )
        {
            return false;
        }
        
        return self::_getEncodedString( $address[1] ) . ' <' . $address[0] . '>';
    }

    /**
     * Подготоваливает массив адресов для вывода, разделяя их запятыми
     * 
     * @param array $addresses Массив адресов
     *
     * @return bool|string Готовая строка или false
     */
    private static function _getEmailAddresses( $addresses )
    {
        if ( empty( $addresses ) || !is_array( $addresses ) )
        {
            return false;
        }
        
        $output = '';
        
        foreach ( $addresses as $address )
        {
            $address_string = self::_getEmailAddress( $address );
            
            if ( $address_string )
            {
                $output .= $address_string . ', ';
            }
        }
        
        return substr( $output, 0, -2 );
    }

    /**
     * Возвращает строку заголовков письма
     * 
     * @param bool $isHtml Является ли письмо форматированным
     *
     * @return bool|string Строка заголовков
     */
    private static function _getHeadersString( $isHtml = false )
    {
        if ( !( $from = self::_getEmailAddress( self::$_from ) ) )
        {
            return false;
        }
        
        $headers = 'Content-type: text/' . ( $isHtml ? 'html' : 'plain' ) .
            '; charset=UTF-8' . "\r\n" .
            'From: ' . $from . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        
        if ( self::$_replyTo )
        {
            $headers .= "\r\n" . 'Reply-To: ' . self::_getEmailAddress( self::$_replyTo );
        }
        
        if ( !empty( self::$_additionalHeaders )
            && is_array( self::$_additionalHeaders ) )
        {
            foreach ( self::$_additionalHeaders as $key => $val )
            {
                $headers .= "\r\n" . $key . ': ' . $val;
            }
        }
        
        return $headers;
    }

    /**
     * Проверка, является ли письмо форматированным с помощью HTML
     * 
     * @param string $message Текст письма
     *
     * @return bool Является ли HTML
     */
    private static function _testHtml( $message )
    {
        return !!( preg_match( '/<html[^>]*>/si', $message ) );
    }

    /**
     * Отправка письма через функцию php mail()
     * 
     * @param string $subject Тема писбма
     * @param string $message Текст сообщения
     *
     * @return bool Успешность отправки
     */
    private static function _sendInternal( $subject, $message )
    {
        if ( !( $to = self::_getEmailAddresses( self::$_recipients ) )
            || !( $headers = self::_getHeadersString( self::_testHtml( $message ) ) ) )
        {
            return false;
        }
        
        return mail(
            $to,
            self::_getEncodedString( $subject ),
            $message,
            $headers,
            self::$_additionalParameters
        );
    }

    /**
     * Отправка письма програмно через сокеты
     * 
     * @param string $subject Тема писбма
     * @param string $message Текст сообщения
     *
     * @return bool Успешность отправки
     */
    private static function _sendSocket( $subject, $message )
    {
        if ( !( $to = self::_getEmailAddresses( self::$_recipients ) )
            || !( $headers = self::_getHeadersString( self::_testHtml( $message ) ) )
            || empty( self::$_from ) || !is_array( self::$_from ) )
        {
            return false;
        }
        
        $server = self::$_socketServer;
        $port = self::$_socketPort;
        
        $socket = fsockopen( $server, $port, $errno, $errstr, 30 );
        
        if ( !$socket )
        {
            self::_logError( "Could not connect to SMTP host {$server}:{$port} ($errno): $errstr" );
            return false;
        }
        
        try
        {
            self::_checkSocketResponse( $socket, 'Connect', '220' ) OR self::_dummyException();
            
            fputs( $socket, "EHLO {$server}\r\n" );
            
            if ( !self::_checkSocketResponse( $socket, 'EHLO', '250', true ) )
            {
                fputs( $socket, "HELO {$server}\r\n" );
                self::_checkSocketResponse( $socket, 'HELO', '250' ) OR self::_dummyException();
            }
            
            if ( self::$_socketLogin )
            {
                fputs( $socket, "AUTH LOGIN\r\n" );
                self::_checkSocketResponse( $socket, 'AUTH', '334' ) OR self::_dummyException();
                
                fputs( $socket, base64_encode( self::$_socketLogin ) . "\r\n" );
                self::_checkSocketResponse( $socket, 'User', '334' ) OR self::_dummyException();
                
                fputs( $socket, base64_encode( self::$_socketPassword ) . "\r\n" );
                self::_checkSocketResponse( $socket, 'Password', '235' ) OR self::_dummyException();
            }
            
            fputs( $socket, 'MAIL FROM: <' . self::$_from[0] . ">\r\n" );
            self::_checkSocketResponse( $socket, 'MAIL', '250' ) OR self::_dummyException();
            
            foreach ( self::$_recipients as $recipient )
            {
                if ( ! empty( $recipient ) && is_array( $recipient ) && !empty( $recipient[0] ) )
                {
                    fputs( $socket, 'RCPT TO: <' . $recipient[0] . ">\r\n" );
                    self::_checkSocketResponse( $socket, 'RCPT', '250' ) OR self::_dummyException();
                }
            }
            
            fputs( $socket, "DATA\r\n" );
            self::_checkSocketResponse( $socket, 'DATA', '354' ) OR self::_dummyException();
            
            fputs( $socket, $headers . "\r\n" );
            fputs( $socket, "To: $to\r\n" );
            fputs( $socket, 'Date: ' . date( 'r' ) . "\r\n" );
            fputs( $socket, "MIME-Version: 1.0\r\n" );
            fputs( $socket, 'Subject: ' . self::_getEncodedString( $subject ) . "\r\n" );
            fputs( $socket, "\r\n\r\n" );
            fputs( $socket, $message . "\r\n" );
            fputs( $socket, ".\r\n" );
            self::_checkSocketResponse( $socket, 'End data', '250' ) OR self::_dummyException();
            
            fputs( $socket, "QUIT\r\n" );
            fclose( $socket );
        }
        catch ( SendMailDummyException $e )
        {
            fputs( $socket, "QUIT\r\n" );
            fclose( $socket );
            return false;
        }
        
        return true;
    }

    /**
     * Проверить ответ сервера при отправке через сокеты
     * 
     * @param resource $socket Открытый сокет
     * @param string $title Подпись к выполняемому действию (для логов)
     * @param string $expected Ожидаемый код ответа
     * @param bool $skip_error Не писать ошибку в лог
     * @return bool
     */
    private static function _checkSocketResponse( $socket, $title, $expected, $skip_error = false )
    {
        $response = '';
        $i = 100;
        
        while ( ( substr( $response, 3, 1 ) !== ' ' ) && ( $i > 0 ) )
        {
            $response = fgets( $socket, 256 );
            
            if ( !$response )
            {
                self::_logError( $title . ': Couldn\'t get mail server response.' );
                return false;
            }
        }
        
        if ( substr( $response, 0, 3 ) !== $expected )
        {
            if ( !$skip_error )
            {
                self::_logError( $title . ': ' . $response );
            }
            
            return false;
        }
        
        return true;
    }
    
    private static function _dummyException()
    {
        throw new SendMailDummyException( '' );
    }

    /**
     * Записать сообщение об ошибке
     * @param string $message
     */
    private static function _logError( $message )
    {
        trigger_error( '[SendMail] ' . $message );
    }
}

/**
 * Class SendMailDummyException
 */
class SendMailDummyException extends Exception {}

