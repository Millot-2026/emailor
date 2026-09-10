<?php
/**
 * ajax/save-template.php - Sauvegarde des données et compilation du fichier HTML email
 */
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Données invalides.']);
    exit;
}

$id = $input['id'] ?? '';
$name = trim($input['name'] ?? 'Sans nom');
$subject = trim($input['subject'] ?? '');
$blocks = $input['blocks'] ?? [];

// Générer un ID unique si inexistant
if (empty($id)) {
    $id = 'tpl_' . uniqid();
}

$dataFile = __DIR__ . '/../data/templates.json';
$templates = [];

if (file_exists($dataFile)) {
    $templates = json_decode(file_get_contents($dataFile), true) ?: [];
}

// Enregistrement des données du template
$templates[$id] = [
    'name' => $name,
    'subject' => $subject,
    'blocks' => $blocks,
    'updated_at' => date('Y-m-d H:i:s')
];

file_put_contents($dataFile, json_encode($templates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Compilation du code HTML e-mail (structure compatible clients mail avec tables)
$htmlContent = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . htmlspecialchars($subject) . '</title></head>';
$htmlContent .= '<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">';
$htmlContent .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; background-color: #f4f4f4; padding: 20px 0; min-height: 100vh;">';
$htmlContent .= '<tr><td align="center">';
$htmlContent .= '<table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">';
$htmlContent .= '<tr><td style="padding: 30px;">';

foreach ($blocks as $block) {
    switch ($block['type']) {
        case 'header':
            $htmlContent .= '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="center" style="padding-bottom: 20px; font-size: 24px; font-weight: bold; color: #333333;">' . htmlspecialchars($block['content'] ?? 'Logo / En-tête') . '</td></tr></table>';
            break;
        case 'title':
            $htmlContent .= '<h1 style="color: #111111; font-size: 22px; margin-top: 0; margin-bottom: 15px;">' . htmlspecialchars($block['content'] ?? '') . '</h1>';
            break;
        case 'text':
            $htmlContent .= '<p style="color: #555555; font-size: 15px; line-height: 1.5; margin-top: 0; margin-bottom: 15px;">' . nl2br(htmlspecialchars($block['content'] ?? '')) . '</p>';
            break;
        case 'button':
            $btnText = htmlspecialchars($block['text'] ?? 'Cliquez ici');
            $btnUrl = htmlspecialchars($block['url'] ?? '#');
            $htmlContent .= '<table border="0" cellspacing="0" cellpadding="0" style="margin: 20px 0;"><tr><td align="center" bgcolor="#007bff" style="border-radius: 4px;"><a href="' . $btnUrl . '" target="_blank" style="font-size: 15px; font-weight: bold; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 4px; border: 1px solid #007bff; display: inline-block;">' . $btnText . '</a></td></tr></table>';
            break;
        case 'image':
            $imgUrl = htmlspecialchars($block['url'] ?? '');
            if ($imgUrl) {
                $htmlContent .= '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="center" style="padding-bottom: 15px;"><img src="' . $imgUrl . '" alt="" style="max-width: 100%; height: auto; display: block; border: 0;"></td></tr></table>';
            }
            break;
        case 'columns-2':
            $leftText = nl2br(htmlspecialchars($block['leftContent'] ?? ''));
            $rightText = nl2br(htmlspecialchars($block['rightContent'] ?? ''));
            $htmlContent .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 15px;">';
            $htmlContent .= '<tr>';
            $htmlContent .= '<td align="left" valign="top" width="100%" style="padding-bottom: 15px;">';
            $htmlContent .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="left" style="max-width: 270px; display: inline-block; vertical-align: top;">';
            $htmlContent .= '<tr><td style="color: #555555; font-size: 15px; line-height: 1.5; padding-right: 10px;">' . $leftText . '</td></tr>';
            $htmlContent .= '</table>';
            $htmlContent .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="left" style="max-width: 270px; display: inline-block; vertical-align: top;">';
            $htmlContent .= '<tr><td style="color: #555555; font-size: 15px; line-height: 1.5; padding-left: 10px;">' . $rightText . '</td></tr>';
            $htmlContent .= '</table>';
            $htmlContent .= '</td>';
            $htmlContent .= '</tr>';
            $htmlContent .= '</table>';
            break;
        case 'spacer':
            $htmlContent .= '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td style="padding-bottom: 20px; border-bottom: 1px solid #eeeeee; margin: 20px 0;">&nbsp;</td></tr></table>';
            break;
    }
}

$htmlContent .= '</td></tr></table>';
$htmlContent .= '</td></tr></table>';
$htmlContent .= '</body></html>';

// Écriture du fichier HTML compilé final
$htmlDir = __DIR__ . '/../template/';
if (!is_dir($htmlDir)) {
    mkdir($htmlDir, 0777, true);
}
file_put_contents($htmlDir . $id . '.html', $htmlContent);

echo json_encode(['success' => true, 'id' => $id]);