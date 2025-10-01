<?php
/*
// test_mailer.php
require_once __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'capstoneesang@gmail.com'; // Sender Gmail address
    $mail->Password   = 'YOUR_APP_PASSWORD';    // App Password for capstoneesang@gmail.com
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('capstoneesang@gmail.com', 'Esang Delicacies');
    $mail->addAddress('crabholesociety@gmail.com'); // Customer email

    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer Test';
    $mail->Body    = 'This is a test email from PHPMailer.';
    $mail->AltBody = 'This is a test email from PHPMailer.';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . "\n";
    echo 'Exception: ' . $e->getMessage() . "\n";
}
*/
