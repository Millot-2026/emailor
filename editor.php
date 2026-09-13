<?php
/**
 * editor.php - Interface de création et modification des templates
 */
$dataFile = __DIR__ . '/data/templates.json';
$templates = [];

if (file_exists($dataFile)) {
    $templates = json_decode(file_get_contents($dataFile), true) ?: [];
}

$id = $_GET['id'] ?? '';
$currentTemplate = [
    'name' => 'Nouveau Template',
    'subject' => '',
    'blocks' => []
];

if ($id && isset($templates[$id])) {
    $currentTemplate = $templates[$id];
}

// Chargement dynamique des contacts depuis le répertoire
$contactFile = __DIR__ . '/data/contacts.json';
$allContacts = file_exists($contactFile) ? (json_decode(file_get_contents($contactFile), true) ?: []) : [];

// Ne conserver que les contacts marqués comme actifs
$activeEmails = [];
foreach ($allContacts as $c) {
    if ($c['active'] ?? true) {
        $activeEmails[] = $c['email'];
    }
}
$defaultEmail = $activeEmails[0] ?? 'cmillot2004@gmail.com';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Éditeur de Template d'E-mail</title>
    <link rel="stylesheet" href="assets/css/editor.css">
    <style>
        .block-toolbox {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .btn-fini {
            position: relative;
            overflow: hidden;
            width: 100%;
            height: 48px;
            background: #d48f2f;
            background: -webkit-linear-gradient(90deg,rgba(212, 143, 47, 1) 0%, rgba(112, 70, 11, 1) 15%, rgba(153, 99, 24, 1) 25%, rgba(212, 143, 47, 1) 50%, rgba(153, 99, 24, 1) 75%, rgba(112, 70, 11, 1) 85%, rgba(212, 143, 47, 1) 100%);
            background: -moz-linear-gradient(90deg,rgba(212, 143, 47, 1) 0%, rgba(112, 70, 11, 1) 15%, rgba(153, 99, 24, 1) 25%, rgba(212, 143, 47, 1) 50%, rgba(153, 99, 24, 1) 75%, rgba(112, 70, 11, 1) 85%, rgba(212, 143, 47, 1) 100%);
            background: linear-gradient(90deg,rgba(212, 143, 47, 1) 0%, rgba(112, 70, 11, 1) 15%, rgba(153, 99, 24, 1) 25%, rgba(212, 143, 47, 1) 50%, rgba(153, 99, 24, 1) 75%, rgba(112, 70, 11, 1) 85%, rgba(212, 143, 47, 1) 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(
              startColorstr="#D48F2F",
              endColorstr="#D48F2F",
              GradientType=1
            );
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            border: none;
            cursor: pointer;
            box-sizing: border-box;
            text-transform: uppercase;
        }

        .btn-fini::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s ease-in-out;
            pointer-events: none;
            z-index: 1;
        }

        .btn-fini:hover::before {
            left: 100%;
        }

        .btn-fini:hover {
            position: relative;
            width: 100%;
            height: 48px;
            background: #966724;
            background: linear-gradient(
                    90deg,
                    rgb(160, 108, 34) 0%,
                    rgba(112, 70, 11, 1) 15%,
                    rgba(153, 99, 24, 1) 25%,
                    rgb(98, 63, 13) 50%,
                    rgba(153, 99, 24, 1) 75%,
                    rgba(112, 70, 11, 1) 85%,
                    rgb(160, 108, 34) 100%);
            
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 10px 12px rgba(0,0,0,0.2);
            border: none;
            cursor: pointer;
            box-sizing: border-box;
            text-transform: uppercase;
        }
        .btn-fini .rivet-gauche,
        .btn-fini .rivet-droite {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #ffe6a0, #b8862f 50%, #5e330a 100%);
            box-shadow: 0 5px 5px rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }
        .btn-fini .rivet-gauche::after,
        .btn-fini .rivet-droite::after {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #fff, #99621b);
            box-shadow: inset 0 1px 2px rgba(255,255,255,0.8), 0 1px 2px rgba(0,0,0,0.5);
        }
        .btn-fini .rivet-gauche {
            left: 14px;
        }
        .btn-fini .rivet-droite {
            right: 14px;
        }
    </style>
</head>
<body>
    <div class="editor-layout" id="editorLayout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="index.php" class="btn btn-secondary">&larr; Retour</a>
                <h2>Éditeur</h2>
            </div>
            
            <div class="form-group">
                <label for="template-name">Nom du template :</label>
                <input type="text" id="template-name" value="<?= htmlspecialchars($currentTemplate['name']) ?>">
            </div>

            <div class="form-group">
                <label for="email-subject">Objet de l'e-mail :</label>
                <input type="text" id="email-subject" value="<?= htmlspecialchars($currentTemplate['subject'] ?? '') ?>">
            </div>

            <hr>

            <h3 class="btn btn-secondary btn-full">Ajouter un bloc</h3>
            <div class="block-toolbox">
                <button type="button" onclick="addBlock('header')" class="btn-fini">
                    <span class="rivet-gauche"></span>
                    + En-tête / Logo
                    <span class="rivet-droite"></span>
                </button>
                <button type="button" onclick="addBlock('title')" class="btn-fini">
                    <span class="rivet-gauche"></span>
                    + Titre H1
                    <span class="rivet-droite"></span>
                </button>
                <button type="button" onclick="addBlock('text')" class="btn-fini">
                    <span class="rivet-gauche"></span>
                    + Paragraphe
                    <span class="rivet-droite"></span>
                </button>
                <button type="button" onclick="addBlock('button')" class="btn-fini">
                    <span class="rivet-gauche"></span>
                    + Bouton CTA
                    <span class="rivet-droite"></span>
                </button>
                <button type="button" onclick="addBlock('image')" class="btn-fini">
                    <span class="rivet-gauche"></span>
                    + Image
                    <span class="rivet-droite"></span>
                </button>
                <button type="button" onclick="addBlock('columns-2')" class="btn-fini">
                    <span class="rivet-gauche"></span>
                    + 2 Colonnes
                    <span class="rivet-droite"></span>
                </button>
                <button type="button" onclick="addBlock('spacer')" class="btn-fini">
                    <span class="rivet-gauche"></span>
                    + Séparateur
                    <span class="rivet-droite"></span>
                </button>
            </div>

            <hr>

            <h3 class="btn btn-secondary btn-full">Envoyer un test</h3>
            <div class="form-group">
                <label for="test-email">Adresse e-mail :</label>
                <input type="text" id="test-email" placeholder="votre@email.com" value="<?= htmlspecialchars($defaultEmail) ?>">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: normal; cursor: pointer; line-height: 1.3;">
                    <input type="checkbox" id="select-all-emails" onchange="toggleAllEmails(this)" style="margin: 0; flex-shrink: 0; width: 16px; height: 16px;">
                    <span>Répertoire actif</span>
                </label>
                <div style="font-size: 11px; color: #888; margin-top: 3px;">(<a href="directory.php" target="_blank" style="color: #007bff; text-decoration: none;">Gérer les contacts</a>)</div>
            </div>

            <button onclick="sendTestEmail('<?= $id ?>')" class="btn btn-secondary btn-full">Envoyer le test</button>

            <div class="actions-save" style="margin-top: 15px; display: flex; gap: 10px;">
                <?php if ($id && file_exists(__DIR__ . '/template/' . $id . '.html')): ?>
                    <a href="template/<?= $id ?>.html" target="_blank" class="btn btn-secondary" style="flex: 1; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">Prévisualiser</a>
                <?php else: ?>
                    <button onclick="alert('Veuillez d\'abord enregistrer le template pour pouvoir le prévisualiser.');" class="btn btn-secondary" style="flex: 1;">Prévisualiser</button>
                <?php endif; ?>
                <button onclick="saveTemplate('<?= $id ?>')" class="btn btn-primary" style="flex: 1;">Enregistrer</button>
            </div>
        </aside>

        <main class="preview-area">
            <div class="preview-toolbar">
                <button type="button" class="sidebar-floating-btn" id="sidebarFloatingToggle" title="Rétracter/Déployer le poste de pilotage">⚙</button>
                <span>Prévisualisation en direct</span>
            </div>
            <div id="email-canvas" class="email-canvas">
                <!-- Les blocs dynamiques s'insèrent ici -->
            </div>
        </main>
    </div>

    <script>
        const initialData = <?= json_encode($currentTemplate['blocks'] ?? []) ?>;
        const activeDirectoryEmails = <?= json_encode($activeEmails) ?>;
        
        function toggleAllEmails(checkbox) {
            const emailInput = document.getElementById('test-email');
            if (checkbox.checked) {
                emailInput.value = activeDirectoryEmails.join(', ');
            } else {
                emailInput.value = activeDirectoryEmails[0] || '';
            }
        }

        function sendTestEmail(id) {
            if (!id) {
                alert("Veuillez d'abord enregistrer le template avant d'envoyer un test.");
                return;
            }
            const email = document.getElementById('test-email').value;
            if (!email) {
                alert("Veuillez saisir une adresse e-mail de destinataire.");
                return;
            }

            fetch('ajax/send-email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, to: email })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('E-mail envoyé avec succès !');
                } else {
                    alert('Erreur lors de l\'envoi : ' + (result.error || 'Inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur réseau lors de l\'envoi.');
            });
        }
    </script>

    <script>
    document.getElementById('sidebarFloatingToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
    });
    </script>

    <script src="assets/js/editor.js"></script>
</body>
</html>