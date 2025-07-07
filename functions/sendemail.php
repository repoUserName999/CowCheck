<?php 
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
function sendResetEmail ($toEmail, $token) {
  
$env = parse_ini_file(__DIR__ . '/../config/.env');
$resetLink = "http://localhost/webprojects/bobsautoparts/reset_pwd.php?token=$token";
$subject = "Reset Your password";
$message = "Click this link to reset your password: $resetLink";

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();                                    
    $mail->Host       = 'smtp.gmail.com';               
    $mail->SMTPAuth   = true;                           
    $mail->Username   = $env['GM_USER'];          
    $mail->Password   = $env['GM_PASS'];         
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port       = 587; 

    $mail->setFrom('yourgmail@gmail.com', 'CowCheck');  
    $mail->addAddress($toEmail); 

     $mail->isHTML(false); 
    $mail->Subject = $subject;
    $mail->Body    = $message;

    $mail->send();
    return true;
} catch (Exception $e) {
        return "Mailer Error: {$mail->ErrorInfo}";
    }
}
?>