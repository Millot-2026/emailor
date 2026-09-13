<?php
/**
 * save-template.php - Enregistrement des données JSON et génération du fichier HTML du template
 */
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Données invalides.']);
    exit;
}

$id = $input['id'] ?? '';
$name = trim($input['name'] ?? 'Nouveau Template');
$subject = trim($input['subject'] ?? '');
$blocks = $input['blocks'] ?? [];

$dataFile = __DIR__ . '/../data/templates.json';
$templates = file_exists($dataFile) ? (json_decode(file_get_contents($dataFile), true) ?: []) : [];

if (empty($id)) {
    $id = 'tpl_' . substr(uniqid(), 0, 13);
}

$templates[$id] = [
    'name' => $name,
    'subject' => $subject,
    'blocks' => $blocks,
    'updated_at' => date('Y-m-d H:i:s')
];

file_put_contents($dataFile, json_encode($templates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Génération du fichier HTML statique pour la prévisualisation finale avec support responsive des colonnes
$htmlContent = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($name) . '</title>
    <style>
        body { font-family: Georgia, serif; background-color: #f4f1ea; margin: 0; padding: 40px; color: #2b2621; }
        .email-container { max-width: 600px; margin: 0 auto; background: #fdfcf7; padding: 30px; border: 4px solid #8c6747; border-radius: 4px; }
        @media (max-width: 600px) {
            .col-cell { display: block !important; width: 100% !important; padding: 0 !important; padding-bottom: 15px !important; }
        }
    </style>
</head>
<body>
    <div class="email-container">
';

foreach ($blocks as $block) {
    switch ($block['type'] ?? '') {
        case 'header':
            $htmlContent .= '<div style="text-align: center; font-weight: bold; font-size: 1.2rem; margin-bottom: 20px; color: #8c6747; text-transform: uppercase;">' . htmlspecialchars($block['content'] ?? '') . '</div>';
            break;
        case 'title':
            $htmlContent .= '<h1 style="font-size: 1.5rem; color: #2b2621; margin-bottom: 15px;">' . htmlspecialchars($block['content'] ?? '') . '</h1>';
            break;
        case 'text':
            $htmlContent .= '<p style="line-height: 1.6; color: #444; margin-bottom: 15px;">' . nl2br(htmlspecialchars($block['content'] ?? '')) . '</p>';
            break;
        case 'button':
            $url = htmlspecialchars($block['url'] ?? '#');
            $text = htmlspecialchars($block['text'] ?? 'Cliquez ici');
            $htmlContent .= '<div style="text-align: center; margin: 25px 0;"><a href="' . $url . '" style="background: #d9822b; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 3px; font-weight: bold; display: inline-block;">' . $text . '</a></div>';
            break;
        case 'image':
            $imgUrl = $block['url'] ?? '';
            if ($imgUrl) {
                if (strpos($imgUrl, 'uploads/') === 0) {
                    $imgUrl = '../' . $imgUrl;
                }
                $htmlContent .= '<div style="text-align: center; margin: 20px 0;"><img src="' . htmlspecialchars($imgUrl) . '" style="max-width: 100%; height: auto; border-radius: 3px;"></div>';
            }
            break;
        case 'columns-2':
            $left = nl2br(htmlspecialchars($block['leftContent'] ?? ''));
            $right = nl2br(htmlspecialchars($block['rightContent'] ?? ''));
            $htmlContent .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;"><tr><td class="col-cell" style="width: 50%; vertical-align: top; padding-right: 10px; color: #444;">' . $left . '</td><td class="col-cell" style="width: 50%; vertical-align: top; padding-left: 10px; color: #444;">' . $right . '</td></tr></table>';
            break;
        case 'spacer':
            $htmlContent .= '<hr style="border: none; border-top: 1px dashed #b87333; margin: 25px 0;">';
            break;
    }
}

$htmlContent .= '
    </div>
</body>
</html>';

$templateDir = __DIR__ . '/../template/';
if (!is_dir($templateDir)) {
    mkdir($templateDir, 0755, true);
}
file_put_contents($templateDir . $id . '.html', $htmlContent);

echo json_encode(['success' => true, 'id' => $id]);