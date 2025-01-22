<?php

namespace Tests\Core;

require_once __DIR__ . '/../../libs/PHPMailer-6.9.3/src/Exception.php';
require_once __DIR__ . '/../../libs/PHPMailer-6.9.3/src/PHPMailer.php';
require_once __DIR__ . '/../../libs/PHPMailer-6.9.3/src/SMTP.php';

use Core\EmailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
   
    public function testSendEmail()
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = '127.0.0.1'; // Servidor SMTP local
        $mail->Port = 25; // Puerto SMTP
        $mail->SMTPAuth = false; // Sin autenticación
        $mail->SMTPSecure = '';   // Sin seguridad
        $mail->setFrom('from@example.com', 'User');
        $mail->addAddress('to@example.com', 'Juan User'); // Dirección de destino
        $mail->Subject = 'Test Email';
        $mail->Body    = 'This is a test email sent from Mercury SMTP server.';

        $mail->send();

        $this->assertTrue(true);
    }

    public function testSendEmailFailure()
    {
        $mailMock = $this->getMockBuilder(PHPMailer::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $mailMock->method('send')
                 ->willReturn(false);

        $this->assertFalse(EmailService::sendEmail("Juan Pérez", "juan.perez@ejemplo.com", "Prueba de Envío", "Este es un correo de prueba."));
    }
}
