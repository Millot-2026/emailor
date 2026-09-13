<?php
/**
 * index.php - Liste des templates d'e-mail et accès au répertoire
 */
$dataFile = __DIR__ . '/data/templates.json';
$templates = [];

if (file_exists($dataFile)) {
    $templates = json_decode(file_get_contents($dataFile), true) ?: [];
}

// Gestion de la suppression d'un template
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = $_GET['id'];
    if (isset($templates[$deleteId])) {
        unset($templates[$deleteId]);
        file_put_contents($dataFile, json_encode($templates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $htmlFile = __DIR__ . '/template/' . $deleteId . '.html';
        if (file_exists($htmlFile)) {
            unlink($htmlFile);
        }
    }
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emailor - Gestionnaire de Templates</title>
    <link rel="stylesheet" href="assets/css/editor.css">
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: #1c1917;
            background-image: radial-gradient(#292524 15%, transparent 16%), radial-gradient(#292524 15%, transparent 16%);
            background-size: 16px 16px;
            background-position: 0 0, 8px 8px;
            color: #f8fafc;
            margin: 0;
            padding: 40px;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .emailor-container {
            background-color: #fdfbf7;
            color: #2b2b2b;
            border: 2px solid #2b2b2b;
            border-radius: 4px;
            padding: 35px;
            font-family: Georgia, "Times New Roman", serif;
            max-width: 950px;
            margin: 0 auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            position: relative;
            box-sizing: border-box;
        }

        .emailor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2b2b2b;
            padding-bottom: 15px;
            gap: 15px;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .action-cell {
            text-align: right;
            vertical-align: middle;
            white-space: nowrap;
        }

        .actions-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 5px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 12px;
            text-decoration: none;
            font-size: 13px;
            border-radius: 3px;
            box-sizing: border-box;
            height: 30px;
            font-family: -apple-system, sans-serif;
        }
        .btn-trash {
            background: #2b2b2b;
            border: 1px solid #2b2b2b;
            color: #ffffff;
            width: 30px;
            padding: 0;
            margin-left: 5px;
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-trash:hover {
            background: #c0392b;
            border-color: #c0392b;
        }

        /* Desktop uniquement : utilisation stricte de flex et space-between pour séparer les 3 colonnes */
        @media (min-width: 769px) {
            .desktop-row-flex {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                width: 100% !important;
            }
            .mobile-text-row {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            .emailor-container {
                padding: 15px;
            }
            .emailor-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .emailor-header > div {
                width: 100%;
                flex-wrap: wrap;
            }

            thead {
                display: none;
            }

            tr {
                display: flex;
                flex-direction: column;
                padding: 12px;
                border-bottom: 1px solid #e2ddd5;
            }

            td {
                display: block;
                padding: 0 !important;
                border: none !important;
            }

            .desktop-row-flex {
                display: none !important;
            }

            .mobile-text-row {
                display: flex !important;
                justify-content: space-between;
                align-items: baseline;
                gap: 15px;
                width: 100%;
            }

            .mobile-name {
                font-weight: 600;
                color: #2b2b2b;
            }
            .mobile-subject {
                color: #444;
            }
            .mobile-date {
                color: #666;
                font-size: 0.8rem;
                white-space: nowrap;
            }

            .action-cell {
                display: block !important;
                text-align: left !important;
                margin-top: 10px;
                padding-top: 8px !important;
                border-top: 1px dashed #e2ddd5;
            }
            .actions-wrapper {
                justify-content: flex-start;
                width: 100%;
            }
            .action-btn {
                flex: 1;
            }
            .btn-trash {
                width: auto !important;
                flex: 0 0 35px;
            }
        }
    </style>
</head>
<body>
    <div class="emailor-container">
        <div class="emailor-header">
            <h2 style="margin: 0; font-family: Georgia, serif; text-transform: uppercase; font-size: 1.5rem; letter-spacing: -0.5px;">Mes Templates d'E-mails</h2>
            <div style="display: flex; gap: 10px;">
                <a href="directory.php" class="btn btn-secondary" style="display: inline-flex; align-items: center; height: 30px; box-sizing: border-box;">Gérer le répertoire</a>
                <a href="editor.php" class="btn btn-primary" style="display: inline-flex; align-items: center; height: 30px; box-sizing: border-box;">+ Nouveau template</a>
            </div>
        </div>

        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; font-family: -apple-system, sans-serif;">
                <thead>
                    <tr style="background: #2b2b2b; color: #d99b5b; text-align: left;">
                        <th style="padding: 12px; border-bottom: 2px solid #2b2b2b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Nom</th>
                        <th style="padding: 12px; border-bottom: 2px solid #2b2b2b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Objet</th>
                        <th style="padding: 12px; border-bottom: 2px solid #2b2b2b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Dernière modification</th>
                        <th style="padding: 12px; border-bottom: 2px solid #2b2b2b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($templates)): ?>
                        <tr>
                            <td colspan="4" style="padding: 20px; text-align: center; color: #777; background: #fff;">Aucun template créé pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($templates as $id => $tpl): ?>
                            <tr style="background: #fff; border-bottom: 1px solid #e2ddd5;">
                                <td colspan="3" style="padding: 12px; vertical-align: middle;">
                                    <!-- Version Desktop : Flex avec space-between strict -->
                                    <div class="desktop-row-flex">
                                        <div style="font-weight: 600; color: #2b2b2b;"><?= htmlspecialchars($tpl['name'] ?? 'Sans nom') ?></div>
                                        <div style="color: #444;"><?= htmlspecialchars($tpl['subject'] ?? '') ?></div>
                                        <div style="color: #666; font-size: 0.85rem;"><?= htmlspecialchars($tpl['updated_at'] ?? '-') ?></div>
                                    </div>
                                    <!-- Version Mobile -->
                                    <div class="mobile-text-row">
                                        <span class="mobile-name"><?= htmlspecialchars($tpl['name'] ?? 'Sans nom') ?></span>
                                        <span class="mobile-subject"><?= htmlspecialchars($tpl['subject'] ?? '') ?></span>
                                        <span class="mobile-date"><?= htmlspecialchars($tpl['updated_at'] ?? '-') ?></span>
                                    </div>
                                </td>
                                <td style="padding: 12px;" class="action-cell">
                                    <div class="actions-wrapper">
                                        <a href="editor.php?id=<?= $id ?>" class="btn btn-secondary action-btn">Éditer</a>
                                        <?php if (file_exists(__DIR__ . '/template/' . $id . '.html')): ?>
                                            <a href="template/<?= $id ?>.html" target="_blank" class="btn btn-secondary action-btn" style="margin-left: 5px;">Voir</a>
                                        <?php endif; ?>
                                        <a href="index.php?action=delete&id=<?= $id ?>" class="btn action-btn btn-trash" title="Supprimer le template" onclick="return confirm('Voulez-vous vraiment supprimer ce template ?');">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>