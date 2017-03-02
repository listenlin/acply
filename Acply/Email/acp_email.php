<?php
/**
 *
 */
class Acp_email extends Acp_base
{

    // 异常信息
    public static $error;

    public static function send($to, $subject, $message)
    {
        try {
            include_once __DIR__ . '/phpmailer.php';
            $phpmailer = new PHPMailer;
            $phpmailer->CharSet = 'UTF-8';

             $phpmailer->SMTPDebug = 3;                     // Enable verbose debug output

            $phpmailer->isSMTP();                           // Set mailer to use SMTP
            $phpmailer->Host = 'smtpcloud.sohu.com';        // Specify main and backup SMTP servers
            $phpmailer->SMTPAuth = true;                    // Enable SMTP authentication
            $phpmailer->Username = 'tianyan_test_E6Z5oD';       // SMTP username
            $phpmailer->Password = 'AGaYBQTkEu6SmsTR';  // SMTP password
            //$phpmailer->SMTPSecure = 'ssl';				// Enable TLS encryption, `ssl` also accepted
            $phpmailer->Port = 25;                      // TCP port to connect to

            $phpmailer->From = 'email@weily.org';
            $phpmailer->FromName = '天眼系统';
            $phpmailer->addAddress($to);                    // Add a recipient
            $phpmailer->addReplyTo('email@weily.org', '天眼系统');

            $phpmailer->WordWrap = 50;                  // Set word wrap to 50 characters
            $phpmailer->isHTML(true);                       // Set email format to HTML

            $phpmailer->Subject = $subject;
            $phpmailer->Body    = $message;
            // $phpmailer->AltBody = 'This is the body in plain text for non-HTML mail clients';
       
            $result = $phpmailer->send();
        } catch (Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
        if (!$result) {
            self::$error = $phpmailer->ErrorInfo;
            return false;
        } else {
            return true;
        }
    }
}
