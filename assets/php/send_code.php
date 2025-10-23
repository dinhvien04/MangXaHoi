<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$smtpConfig = require 'smtp_config.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

function sendCode($email,$subject,$code){
global $mail, $smtpConfig;
    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $smtpConfig['username'];                     //SMTP username
        $mail->Password   = $smtpConfig['password'];                     //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
        $mail->setFrom('vanthangnguyen269@gmail.com', 'Handbook');    //Add a recipient
        $mail->addAddress($email);               //Name is optional
    
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $template = file_get_contents(__DIR__ . '/email_template.html');
        $template = str_replace('{{CODE}}', $code, $template);
        $mail->Body = $template;
        $mail->addEmbeddedImage(__DIR__ . '/../images/icon.png', 'logo');
        $mail->send();
    } catch (Exception $e) {
        echo "Không thể gửi tin nhắn. Lỗi Mailer:{$mail->ErrorInfo}";
    }
    
}
