<?php
/**
 * index.php - Tableau de bord de l'éditeur de mails
 */
$dataFile = __DIR__ . '/data/templates.json';
$templates = [];

if (file_exists($dataFile)) {
    $jsonContent = file_get_contents($dataFile);
    $templates = json_decode($jsonContent, true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestionnaire de Templates de Mails</title>
    <link rel="stylesheet" href="assets/css/editor.css">
</head>
<body>
    <div class="container">
        <header class="dashboard-header">
            <h1>Mes Templates d'E-mails</h1>
            <a href="editor.php" class="btn btn-primary">+ Créer un nouveau template</a>
        </header>

        <section class="template-list">
            <?php if (empty($templates)): ?>
                <p class="no-template">Aucun template enregistré pour le moment. Créez votre premier modèle !</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nom du Template</th>
                            <th>Dernière modification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $id => $tpl): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($tpl['name']) ?></strong></td>
                                <td><?= htmlspecialchars($tpl['updated_at'] ?? 'Inconnue') ?></td>
                                <td class="actions">
                                    <a href="editor.php?id=<?= urlencode($id) ?>" class="btn btn-small">Modifier</a>
                                    <a href="preview.php?id=<?= urlencode($id) ?>" target="_blank" class="btn btn-small btn-secondary">Prévisualiser</a>
                                    <button onclick="deleteTemplate('<?= $id ?>')" class="btn btn-small btn-danger">Supprimer</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>

    <script>
    function deleteTemplate(id) {
        if (confirm("Voulez-vous vraiment supprimer ce template ?")) {
            fetch('ajax/delete-template.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Erreur lors de la suppression.");
                }
            });
        }
    }
    </script>
</body>
</html>