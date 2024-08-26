<?php

require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function createMailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username   = 'rio66947792@gmail.com';
    $mail->Password   = 'hxjs zrdp qzel oliu';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom('rio66947792@gmail.com', 'Emaya.ID');
    return $mail;
}

function sendEmail($to, $subject, $body) {
    $mail = createMailer();
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    try {
        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Gagal mengirim email. Mailer Error: {$mail->ErrorInfo}";
    }
}

function sendVerificationEmail($to, $verification_code) {
    $subject = 'Email Verification';
    $body = "Your verification code is: $verification_code";
    return sendEmail($to, $subject, $body);
}

function resendVerificationCode($email, $conn) {
    $verification_code = rand(1000, 9999);

    $update_code = $conn->prepare("UPDATE `users` SET verification_code = ? WHERE email = ?");
    $update_code->execute([$verification_code, $email]);

    return sendVerificationEmail($email, $verification_code);
}

function sendPasswordResetEmail($to, $reset_link) {
    $subject = 'Password Reset Request';
    $body = "Click the following link to reset your password: <a href='$reset_link'>Reset Password</a>";
    return sendEmail($to, $subject, $body);
}

?>
