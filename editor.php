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

// Répertoire d'adresses e-mail de test
$testEmails = [
    'cmillot2004@gmail.com',
    'contact1@example.com',
    'contact2@example.com'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Éditeur de Template d'E-mail</title>
    <link rel="stylesheet" href="assets/css/editor.css">
</head>
<body>
    <div class="editor-layout">
        <aside class="sidebar">
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

            <h3>Ajouter un bloc</h3>
            <div class="block-toolbox">
                <button onclick="addBlock('header')" class="btn btn-block">+ En-tête / Logo</button>
                <button onclick="addBlock('title')" class="btn btn-block">+ Titre H1</button>
                <button onclick="addBlock('text')" class="btn btn-block">+ Paragraphe</button>
                <button onclick="addBlock('button')" class="btn btn-block">+ Bouton CTA</button>
                <button onclick="addBlock('image')" class="btn btn-block">+ Image</button>
                <button onclick="addBlock('columns-2')" class="btn btn-block">+ 2 Colonnes</button>
                <button onclick="addBlock('spacer')" class="btn btn-block">+ Séparateur</button>
            </div>

            <hr>

            <h3>Envoyer un test</h3>
            <div class="form-group">
                <label for="test-email">Adresse e-mail :</label>
                <input type="text" id="test-email" placeholder="votre@email.com" value="cmillot2004@gmail.com">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: normal; cursor: pointer; line-height: 1.3;">
                    <input type="checkbox" id="select-all-emails" onchange="toggleAllEmails(this)" style="margin: 0; flex-shrink: 0; width: 16px; height: 16px;">
                    <span>Toutes les adresses du répertoire</span>
                </label>
            </div>

            <button onclick="sendTestEmail('<?= $id ?>')" class="btn btn-secondary btn-full">Envoyer le test</button>

            <div class="actions-save" style="margin-top: 15px; display: flex; gap: 10px;">
                <?php if ($id && file_exists(__DIR__ . '/template/' . $id . '.html')): ?>
                    <a href="template/<?= $id ?>.html" target="_blank" class="btn btn-secondary" style="flex: 1; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">Prévisualiser</a>
                <?php else: ?>
                    <button onclick="alert('Veuillez d\'abord enregistrer le template pour pouvoir le prévisualiser.');" class="btn btn-secondary" style="flex: 1;">Prévisualiser</button>
                <?php endif; ?>
                <button onclick="saveTemplate('<?= $id ?>')" class="btn btn-primary" style="flex: 1;">Enregistrer le template</button>
            </div>
        </aside>

        <main class="preview-area">
            <div class="preview-toolbar">
                <span>Prévisualisation en direct</span>
            </div>
            <div id="email-canvas" class="email-canvas">
                <!-- Les blocs dynamiques s'insèrent ici -->
            </div>
        </main>
    </div>

    <script>
        const initialData = <?= json_encode($currentTemplate['blocks'] ?? []) ?>;
        const directoryEmails = <?= json_encode($testEmails) ?>;
        
        function toggleAllEmails(checkbox) {
            const emailInput = document.getElementById('test-email');
            if (checkbox.checked) {
                emailInput.value = directoryEmails.join(', ');
            } else {
                emailInput.value = 'cmillot2004@gmail.com';
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
    <script src="assets/js/editor.js"></script>
</body>
</html>