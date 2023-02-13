<?php
require_once '../vendor/autoload.php';

use Monolog\Level;
use Monolog\Logger;
use Twig\Environment;
use App\Entity\Contact;
use App\Entity\ContactError;
use Twig\Loader\FilesystemLoader;
use Monolog\Handler\StreamHandler;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use App\Controller\ContactController;

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../log/app.log', Level::Debug));
$logger->debug('Le formulaire s\'affiche sur l\'ordinateur : ' . $_SERVER['REMOTE_ADDR']);

$loader = new FilesystemLoader('../templates');
$twig = new Environment($loader, array(
    // 'cache' => '../cache',
));

$contactController = new ContactController();

$contactError = new ContactError();
$contactError->reset();

$contact = new Contact();
$contact->reset();

$sendMailResult = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($contactController->handleRequest($logger, $contact, $contactError) < 1) {
        $sendMailResult = $contactController->sendMail($logger, $contact);
    } 
}
$contactController->index($logger, $contact, $contactError, $twig, $sendMailResult);

