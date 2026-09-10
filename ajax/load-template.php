<?>
<?php
/**
 * ajax/load-template.php - Chargement d'un template spécifique via AJAX
 */
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? $_GET['id'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID manquant.']);
    exit;
}

$dataFile = __DIR__ . '/../data/templates.json';
if (file_exists($dataFile)) {
    $templates = json_decode(file_get_contents($dataFile), true) ?: [];
    if (isset($templates[$id])) {
        echo json_encode(['success' => true, 'template' => $templates[$id]]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Template introuvable.']);