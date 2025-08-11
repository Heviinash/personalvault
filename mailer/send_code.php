<?php

require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';
require_once __DIR__ . '/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function sendVerificationCode($toEmail, $code) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->SMTPDebug = 2; // 🔥 Show detailed debug output
        $mail->Debugoutput = 'html';
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'heviinash@gmail.com'; // your Gmail
        $mail->Password = 'xgigsenbhljxpvnp';   // Gmail App Password — NO SPACES
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('heviinash@gmail.com', 'CyberVault System');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your CyberVault Login Code';
        $mail->Body    = "Your verification code is: <strong>$code</strong>";

        $mail->send();
        echo "<p style='color:green;'>✅ Email sent successfully!</p>";
        return true;
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ Email failed to send: " . $mail->ErrorInfo . "</p>";
        return false;
    }
}

