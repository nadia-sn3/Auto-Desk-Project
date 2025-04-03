<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'backend/Business_Logic/PHPMailer/src/Exception.php';
require 'backend/Business_Logic/PHPMailer/src/PHPMailer.php';
require 'backend/Business_Logic/PHPMailer/src/SMTP.php';

function send_email($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'autodeskproject91@gmail.com'; 
        $mail->Password   = 'linc kkwj nlrd xova';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('autodeskproject91@gmail.com', 'AutoDeskTeam');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return "Email sent successfully!";
    } catch (Exception $e) {
        return "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
