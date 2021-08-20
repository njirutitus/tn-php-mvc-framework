<?php

namespace tn\phpmvc\utils;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer
{
    public string $host = '';
    public string $username = '';
    public string $password = '';
    public int $port = 465;
    public PHPMailer $mail;
    /**
     * Mailer constructor.
     */
    public function __construct(array $config)
    {
        $this->host = $config['host'];
        $this->username = $config['username'];
        $this->password = $config['password'] ?? '';
        $this->port = $config['port'];
        $this->mail = new PHPMailer;
        $this->server();

    }

    public function send() : bool
    {

        try {
            return $this->mail->send();
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
    public function from($users)
    {
        $this->mail->setFrom($users['email'], $users['name']);
    }

    public function to($users)
    {
        foreach($users as $user) //Add a recipient
        {
            $this->mail->addAddress($user['email'], $user['name'] ?? '');
        }
    }

    public function cc($users)
    {
        foreach($users as $user) //Add a recipient
        {
            $this->mail->addCC($user['email'], $user['name']);
        }
    }

    public function bcc($users)
    {
        foreach($users as $user) //Add a recipient
        {
            $this->mail->addBCC($user['email'], $user['name']);
        }
    }

    public function replyTo($users)
    {
        foreach($users as $user) //Add a recipient
        {
            $this->mail->addReplyTo($user['email'], $user['name']);
        }
    }

    public function html($view)
    {
        $this->mail->isHTML($view);                                  //Set email format to HTML
    }

    public function subject($subject)
    {
        $this->mail->Subject = $subject;
    }

    public function body($view)
    {
        $this->mail->Body = $view;

    }

    public function attachments(array $attachments = [])
    {
        //Attachments
        foreach ($attachments as $attachement) {
            if(is_array($attachement))
                $this->mail->addAttachment($attachement['file'], $attachement['name']);    //Optional name
            else $this->mail->addAttachment($attachement);         //Add attachments
        }

    }

    public function server()
    {
        //Server settings
        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $this->mail->isSMTP();                                            //Send using SMTP
        $this->mail->Host       = $this->host;                     //Set the SMTP server to send through
        $this->mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $this->mail->Username   = $this->username;                     //SMTP username
        $this->mail->Password   = $this->password;                               //SMTP password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $this->mail->Port       = $this->port;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    }
}