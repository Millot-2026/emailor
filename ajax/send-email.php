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
$to = trim($input['to'] ?? '');

if (!$id || !$to) {
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

$mail = new PHPMailer(true);

try {
    // Configuration du serveur SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Serveur SMTP (ex: Gmail)
    $mail->SMTPAuth   = true;
    $mail->Username   = 'millot.christophe.2024@gmail.com'; // Ton e-mail
    $mail->Password   = 'smjncaorbfskqbmz'; // Ton mot de passe d'application (16 caractères)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Expéditeur et destinataire
    $mail->setFrom('millot.christophe.2024@gmail.com', 'Mon Application');
    $mail->addAddress($to);

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