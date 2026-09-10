<?php
/**
 * preview.php - Page de rendu plein écran d'un template
 */
$dataFile = __DIR__ . '/data/templates.json';
$id = $_GET['id'] ?? '';

if (!$id || !file_exists($dataFile)) {
    die("Template introuvable.");
}

$templates = json_decode(file_get_contents($dataFile), true) ?: [];

if (!isset($templates[$id])) {
    die("Template introuvable.");
}

$currentTemplate = $templates[$id];
$htmlFile = __DIR__ . '/template/' . $id . '.html';

// Si le fichier HTML compilé existe, on peut l'afficher directement, sinon on affiche un message
if (!file_exists($htmlFile)) {
    die("Le fichier HTML compilé de ce template n'existe pas encore. Veuillez l'enregistrer depuis l'éditeur.");
}

// Lecture et affichage du contenu HTML final de l'e-mail
readfile($htmlFile);