<?php

namespace Core;

require_once __DIR__ . '/../libs/PHPMailer-6.9.3/src/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer-6.9.3/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer-6.9.3/src/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class EmailService
{
    public static function sendEmail(string $name, string $email, string $subject, string $body): bool
    {
        // Retrieve from global settings the required SMTP configuration
        $smtpHost = $GLOBALS['smtp']['host'];
        $smtpPort = $GLOBALS['smtp']['port'];
        $smtpUsername = $GLOBALS['smtp']['username'];
        $smtpPassword = $GLOBALS['smtp']['password'];
        $toEmail = $GLOBALS['smtp']['email'];
        $toName = $GLOBALS['smtp']['name'];

        // Create an instance of PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_LOWLEVEL;    // Enable verbose debug output
            $mail->isSMTP();                            // Send using SMTP
            $mail->Host = $smtpHost;                    // Set the SMTP server to send through
            $mail->SMTPAuth = true;                     // Enable SMTP authentication
            $mail->Username = $smtpUsername;            // SMTP username
            $mail->Password = $smtpPassword;            // SMTP password
            $mail->Port = $smtpPort;                    // TCP port to connect to
            $mail->Timeout = 5;                         // SMTP server timeout in seconds

            // Recipients
            $mail->setFrom($email, $name);
            $mail->addAddress($toEmail, $toName);       // Add a recipient

            // Content
            $mail->isHTML(false);                // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
