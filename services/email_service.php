<?php
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

use phpmailer\phpmailer\PHPMailer;
use phpmailer\phpmailer\Exception;

class EmailService {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        try {
            // Cấu hình SMTP
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'hnqdat2003@gmail.com'; // Email gửi
            $this->mail->Password = 'kxpo paay zyhr zdxw';    // App Password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;

            $this->mail->setFrom('hnqdat2003@gmail.com', 'Restaurant');
            $this->mail->isHTML(true);
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
        }
    }

    public function sendEmail($to, $subject, $body) {
        try {
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;

            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Email Send Error: " . $this->mail->ErrorInfo);
            return false;
        }
    }
}
?>
