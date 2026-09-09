<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

$rootForMailer = dirname(__DIR__, 3);
$composerAutoload = $rootForMailer . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
} elseif (is_file(__DIR__ . '/vendor/PHPMailer/src/PHPMailer.php')) {
    // Compatibility fallback for existing XAMPP clones; Composer remains the preferred path.
    require_once __DIR__ . '/vendor/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/vendor/PHPMailer/src/SMTP.php';
}

function sendCode($email, $subject, $code)
{
    $root = dirname(__DIR__, 3);
    if (!class_exists(PHPMailer::class)) {
        error_log('Handbook mail error: PHPMailer is unavailable. Run composer install.');
        return false;
    }
    $email = normalizeEmail($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $smtpPath = $root . '/config/smtp.php';
    if (!is_file($smtpPath)) {
        error_log('Handbook mail error: config/smtp.php is missing.');
        return false;
    }

    $smtpConfig = require $smtpPath;
    if (!is_array($smtpConfig) || empty($smtpConfig['username']) || empty($smtpConfig['password'])) {
        error_log('Handbook mail error: SMTP credentials are missing.');
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = (string) ($smtpConfig['host'] ?? 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = (string) $smtpConfig['username'];
        $mail->Password = (string) $smtpConfig['password'];
        $encryption = strtolower((string) ($smtpConfig['encryption'] ?? 'smtps'));
        $mail->SMTPSecure = $encryption === 'starttls'
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = (int) ($smtpConfig['port'] ?? ($encryption === 'starttls' ? 587 : 465));
        $mail->CharSet = 'UTF-8';
        $mail->setFrom((string) $smtpConfig['username'], (string) ($smtpConfig['from_name'] ?? 'Handbook'));
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = (string) $subject;

        $template = @file_get_contents(__DIR__ . '/email_template.html');
        if ($template === false) {
            throw new RuntimeException('Email template not found');
        }
        $mail->Body = str_replace('{{CODE}}', e($code), $template);
        $mail->AltBody = 'Mã xác minh Handbook của bạn là: ' . (string) $code;

        $logo = $root . '/public/images/icon.png';
        if (is_file($logo)) {
            $mail->addEmbeddedImage($logo, 'logo');
        }
        $mail->send();
        return true;
    } catch (Throwable $e) {
        $details = $e instanceof Exception ? $mail->ErrorInfo : $e->getMessage();
        error_log('Handbook mail error: ' . $details);
        return false;
    }
}
