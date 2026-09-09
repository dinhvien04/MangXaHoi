<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/PHPMailer/src/SMTP.php';

function sendCode($email, $subject, $code)
{
    $root = dirname(__DIR__, 3);
    $smtpPath = $root . '/config/smtp.php';
    if (!is_file($smtpPath)) {
        error_log('Handbook mail error: config/smtp.php is missing.');
        return false;
    }

    $smtpConfig = require $smtpPath;
    if (empty($smtpConfig['username']) || empty($smtpConfig['password'])) {
        error_log('Handbook mail error: SMTP credentials are missing.');
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $smtpConfig['host'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $smtpConfig['username'];
        $mail->Password = $smtpConfig['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = (int) ($smtpConfig['port'] ?? 465);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($smtpConfig['username'], $smtpConfig['from_name'] ?? 'Handbook');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $template = file_get_contents(__DIR__ . '/email_template.html');
        $mail->Body = str_replace('{{CODE}}', e($code), $template);
        $logo = $root . '/public/images/icon.png';
        if (is_file($logo)) {
            $mail->addEmbeddedImage($logo, 'logo');
        }
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Handbook mail error: ' . $mail->ErrorInfo);
        return false;
    }
}
