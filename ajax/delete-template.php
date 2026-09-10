<?php
/**
 * ajax/delete-template.php - Suppression d'un template et de son fichier HTML associé
 */
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID manquant.']);
    exit;
}

$dataFile = __DIR__ . '/../data/templates.json';
if (file_exists($dataFile)) {
    $templates = json_decode(file_get_contents($dataFile), true) ?: [];
    if (isset($templates[$id])) {
        unset($templates[$id]);
        file_put_contents($dataFile, json_encode($templates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // Supprimer le fichier HTML compilé associé
        $htmlFile = __DIR__ . '/../template/' . $id . '.html';
        if (file_exists($htmlFile)) {
            unlink($htmlFile);
        }

        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Template introuvable.']);