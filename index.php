<?php
/**
 * index.php - Liste des templates d'e-mail et accès au répertoire
 */
$dataFile = __DIR__ . '/data/templates.json';
$templates = [];

if (file_exists($dataFile)) {
    $templates = json_decode(file_get_contents($dataFile), true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emailor - Gestionnaire de Templates</title>
    <link rel="stylesheet" href="assets/css/editor.css">
</head>
<body>
    <div class="editor-layout" style="display: block; max-width: 900px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0;">Mes Templates d'E-mails</h2>
            <div style="display: flex; gap: 10px;">
                <a href="directory.php" class="btn btn-secondary">Gérer le répertoire</a>
                <a href="editor.php" class="btn btn-primary">+ Nouveau template</a>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f1f1f1; text-align: left;">
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;">Nom</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;">Objet</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;">Dernière modification</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($templates)): ?>
                    <tr>
                        <td colspan="4" style="padding: 15px; text-align: center; color: #777;">Aucun template créé pour le moment.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($templates as $id => $tpl): ?>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($tpl['name'] ?? 'Sans nom') ?></td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($tpl['subject'] ?? '') ?></td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($tpl['updated_at'] ?? '-') ?></td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">
                                <a href="editor.php?id=<?= $id ?>" class="btn btn-secondary" style="padding: 5px 10px; text-decoration: none; font-size: 13px;">Éditer</a>
                                <?php if (file_exists(__DIR__ . '/template/' . $id . '.html')): ?>
                                    <a href="template/<?= $id ?>.html" target="_blank" class="btn btn-secondary" style="padding: 5px 10px; text-decoration: none; font-size: 13px; margin-left: 5px;">Voir</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>