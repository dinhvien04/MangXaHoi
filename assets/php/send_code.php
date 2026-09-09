<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$smtpConfig = require __DIR__ . '/smtp_config.php';
$mail = new PHPMailer(true);

function sendCode($email, $subject, $code)
{
    global $mail, $smtpConfig;

    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $smtpConfig['username'];
        $mail->Password = $smtpConfig['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        $mail->clearAllRecipients();
        $mail->clearAttachments();
        $mail->setFrom($smtpConfig['username'], 'Handbook');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $template = file_get_contents(__DIR__ . '/email_template.html');
        $template = str_replace('{{CODE}}', htmlspecialchars((string) $code, ENT_QUOTES, 'UTF-8'), $template);
        $mail->Body = $template;
        $mail->addEmbeddedImage(__DIR__ . '/../images/icon.png', 'logo');
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Handbook mail error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>
