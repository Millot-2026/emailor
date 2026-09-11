<?php
/**
 * send-email.php - Script d'envoi d'e-mail via PHPMailer
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../libs/PHPMailer/src/Exception.php';
require __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../libs/PHPMailer/src/SMTP.php';

// Chargement des identifiants sécurisés hors du code principal
$config = require __DIR__ . '/../config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$templateId = $data['id'] ?? '';
$recipient = $data['to'] ?? '';

if (!$templateId || !$recipient) {
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants.']);
    exit;
}

$templateFile = __DIR__ . '/../template/' . $templateId . '.html';
if (!file_exists($templateFile)) {
    echo json_encode(['success' => false, 'error' => 'Template introuvable.']);
    exit;
}

$htmlContent = file_get_contents($templateFile);

$dataFile = __DIR__ . '/../data/templates.json';
$templates = file_exists($dataFile) ? (json_decode(file_get_contents($dataFile), true) ?: []) : [];
$subject = $templates[$templateId]['subject'] ?? 'Message de votre application';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user'];
    $mail->Password   = $config['smtp_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom($config['smtp_user'], 'Éditeur de Mails');
    
    $emails = array_map('trim', explode(',', $recipient));
    foreach ($emails as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($email);
        }
    }

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $htmlContent;
    $mail->CharSet = 'UTF-8';

    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => "Erreur d'envoi : {$mail->ErrorInfo}"]);
}