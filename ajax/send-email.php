<?php
/**
 * ajax/send-email.php - Envoi réel d'e-mail via PHPMailer et SMTP
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? '';
$toField = trim($input['to'] ?? '');

if (!$id || !$toField) {
    echo json_encode(['success' => false, 'error' => 'ID du template ou destinataire manquant.']);
    exit;
}

$dataFile = __DIR__ . '/../data/templates.json';
if (!file_exists($dataFile)) {
    echo json_encode(['success' => false, 'error' => 'Fichiers de données introuvables.']);
    exit;
}

$templates = json_decode(file_get_contents($dataFile), true) ?: [];
if (!isset($templates[$id])) {
    echo json_encode(['success' => false, 'error' => 'Template introuvable.']);
    exit;
}

$currentTemplate = $templates[$id];
$htmlFile = __DIR__ . '/../template/' . $id . '.html';

if (!file_exists($htmlFile)) {
    echo json_encode(['success' => false, 'error' => 'Le fichier HTML compilé n\'existe pas.']);
    exit;
}

$htmlContent = file_get_contents($htmlFile);
$subject = $currentTemplate['subject'] ?? 'Message';

// Séparer les adresses e-mail par virgule
$recipients = array_map('trim', explode(',', $toField));

$mail = new PHPMailer(true);

try {
    // Configuration du serveur SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'millot.christophe.2024@gmail.com';
    $mail->Password   = 'smjncaorbfskqbmz';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Expéditeur
    $mail->setFrom('millot.christophe.2024@gmail.com', 'Mon Application');

    // Ajouter chaque destinataire proprement
    foreach ($recipients as $to) {
        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($to);
        }
    }

    // Contenu de l'e-mail au format HTML
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $htmlContent;
    $mail->CharSet = 'UTF-8';

    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => "Erreur d'envoi : {$mail->ErrorInfo}"]);
}