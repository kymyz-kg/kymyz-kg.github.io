<?php 
$to      = "test@nk-service.biz";
$subject = "kymyz.kg'den kat";
$message = "Аты: " . filter_var($_POST["name"], FILTER_SANITIZE_STRING);
$message .= "\r\n\r\nE-mail: " . filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
$message .= "\r\n\r\n" . filter_var($_POST["message"], FILTER_SANITIZE_STRING);

$headers = "From: webmaster@kymyz.kg" . "\r\n" . "Reply-To: webmaster@kymyz.kg";

mail($to, $subject, $message, $headers);

header("Location: " . "index.html");

