<?php

namespace App\Controller;

use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class ContactController
{

    public function index($logger, $contact, $contactError, $twig, $sendMailResult="")
    {
        $logger->debug('Before html generating');
        print($twig->render('contact.html.twig', [
            'name' => $contact->name,
            'nameErr' => $contactError->nameErr,
            'email' => $contact->email,
            'emailErr' => $contactError->emailErr,
            'website' => $contact->website,
            'websiteErr' => $contactError->websiteErr,
            'comment' => $contact->comment,
            'gender' => $contact->gender,
            'genderErr' => $contactError->genderErr,
            'navigation' => $contact->navigation,
            'quality' => $contact->quality,
            'sendMailResult' => $sendMailResult,
        ]));
        $logger->debug('After html generating');
    }

    public function handleRequest($logger, $contact, $contactError)
    {
        $logger->debug('Before request handling');

        $error = 0;

        // Form parsing
        if (empty($_POST["name"])) {
            $contactError->nameErr = "Name is required";
            $error++;
        } else {
            $contact->name = $this->test_input($_POST["name"]);
            // check if name only contains letters and whitespace
            if (!preg_match('/^[a-zA-Z \p{L}]+$/ui', $contact->name)) {
                $contactError->nameErr = "Only letters and white space allowed";
            }
        }

        if (empty($_POST["email"])) {
            $contactError->emailErr = "Email is required";
        } else {
            $contact->email = $this->test_input($_POST["email"]);
            // check if e-mail address is well-formed
            if (!filter_var($contact->email, FILTER_VALIDATE_EMAIL)) {
                $contactError->emailErr = "Invalid email format";
                $error++;
            }
        }

        if (empty($_POST["website"])) {
            $contact->website = "";
        } else {
            $contact->website = $this->test_input($_POST["website"]);
            // Check if URL address syntax is valid (this regular expression also allows dashes in the URL)
            if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $contact->website)) {
                $contactError->websiteErr = "Invalid URL";
                $error++;
            }
        }

        if (empty($_POST["comment"])) {
            $contact->comment = "";
        } else {
            $contact->comment = $this->test_input($_POST["comment"]);
        }

        if (empty($_POST["gender"])) {
            $contactError->genderErr = "Gender is required";
            $error++;
        } else {
            $contact->gender = $this->test_input($_POST["gender"]);
        }

        if (!empty($_POST["navigation"])) {
            $contact->navigation = $this->test_input($_POST["navigation"]);
        }

        if (!empty($_POST["quality"])) {
            $contact->quality = $this->test_input($_POST["quality"]);
        }

        $logger->debug('After request handling');
        return $error;
    }

    public function sendMail($logger, $contact)
    {
        $logger->debug('Before mail sending');
        
        $result = "";
        $mail = new PHPMailer(true);
        include('../conf.php');

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $mailHost;                     //Set the SMTP server to send through
            $mail->SMTPAuth   = $mailSMTPAuth;                                   //Enable SMTP authentication
            $mail->Username   = $mailUsername;                     //SMTP username
            $mail->Password   = $mailPassword;                               //SMTP password
            $mail->SMTPSecure = $mailSMTPSecure;            //Enable implicit TLS encryption
            $mail->Port       = $mailPort;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            // Configuring sender information
            $mail->setFrom($mailUsername, 'Formulaire de contact');
            
            // Configuring recipient Information
            $mail->addAddress($mailUsername, 'Contact');
            
            // Define the subject of the email
            $mail->Subject = 'Formulaire de contact - ' . $contact->name;
            
            // Define email content        
            /*    
            $mail->Body = 'Name: ' . $contact->name . "\n\n" .
                          'Email: ' . $contact->email . "\n\n" .
                          'Message: ' . "\n" . $contact->comment . "\n\n" .
                          'Site navigation: ' . $contact->navigation . "\n\n" .
                          'Information quality: ' . $contact->quality;
            */
            $mail->IsHTML(true); 
            $mail->Body = $twig->render('mail-fr.html.twig', [
                'server' => $_SERVER['SERVER_NAME'],
                'mailUsername' => $mailUsername,
                'name' => $contact->name,
                'email' => $contact->email,
                'website' => $contact->website,
                'comment' => $contact->comment,
                'gender' => $contact->gender,
                'navigation' => $contact->navigation,
                'quality' => $contact->quality,    
            ]);  
        
            $mail->send();
            $result = 'Message has been sent';
        } catch (Exception $e) {
            $result = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        
        $logger->debug('After mail sending');

        return $result;
    }

    public function test_input($data): String
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

}
