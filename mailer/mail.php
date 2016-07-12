<?php

    include "SendMail.class.php";

    $subject = "kymyz.kg'ден кат";
    $message = "Аты: " . filter_var($_POST["name"], FILTER_SANITIZE_STRING);
    $message .= "\r\n\r\nE-mail: " . filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $message .= "\r\n\r\n" . filter_var($_POST["message"], FILTER_SANITIZE_STRING);


    mail($to, $subject, $message, $headers);


    SendMail::addRecipient( 'test@nk-service.biz', 'Kymyz.kg' );

    require_once "SendMail.class.php";   

    SendMail::setFrom( 'kymyz.kg@yandex.ru', 'Зыяратчы' );
    SendMail::useSocketMail( 'ssl://smtp.yandex.ru', 465, 'kymyz.kg@yandex.ru', 'kymyzkg110' );
    $result = SendMail::send( $subject, $message);


    return $result;
