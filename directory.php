<?php
/**
 * directory.php - Gestion du répertoire des adresses e-mail de test
 */
$dataFile = __DIR__ . '/data/contacts.json';
$contacts = [];

if (file_exists($dataFile)) {
    $contacts = json_decode(file_get_contents($dataFile), true) ?: [];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse e-mail invalide.';
        } else {
            $contacts[] = [
                'id' => 'contact_' . uniqid(),
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'active' => true
            ];
            file_put_contents($dataFile, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = 'Contact ajouté avec succès.';
        }
    } elseif ($action === 'edit') {
        $contactId = $_POST['contact_id'] ?? '';
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse e-mail invalide pour la modification.';
        } else {
            foreach ($contacts as &$c) {
                if ($c['id'] === $contactId) {
                    $c['nom'] = $nom;
                    $c['prenom'] = $prenom;
                    $c['email'] = $email;
                    break;
                }
            }
            unset($c);
            file_put_contents($dataFile, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = 'Contact modifié avec succès.';
        }
    } elseif ($action === 'delete') {
        $contactId = $_POST['contact_id'] ?? '';
        $contacts = array_filter($contacts, function($c) use ($contactId) {
            return $c['id'] !== $contactId;
        });
        $contacts = array_values($contacts);
        file_put_contents($dataFile, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $success = 'Contact supprimé avec succès.';
    } elseif ($action === 'update_status') {
        $activeIds = $_POST['active_contacts'] ?? [];
        foreach ($contacts as &$contact) {
            $contact['active'] = in_array($contact['id'], $activeIds);
        }
        unset($contact);
        file_put_contents($dataFile, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $success = 'Sélection des contacts mise à jour.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Répertoire des contacts - Emailor</title>
    <link rel="stylesheet" href="assets/css/editor.css">
</head>
<body>
    <div class="editor-layout" style="display: block; max-width: 900px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <div class="sidebar-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <a href="index.php" class="btn btn-secondary">&larr; Retour</a>
            <h2>Répertoire des e-mails de test</h2>
        </div>

        <?php if ($error): ?>
            <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" style="background: #f9f9f9; padding: 20px; border-radius: 4px; margin-bottom: 30px;">
            <input type="hidden" name="action" value="add">
            <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 16px;">Ajouter un nouveau contact</h3>
            
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;" class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div style="flex: 1;" class="form-group">
                    <label for="prenom">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="email">Adresse e-mail :</label>
                <input type="email" id="email" name="email" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <button type="submit" class="btn btn-primary">Ajouter au répertoire</button>
        </form>

        <form method="POST">
            <input type="hidden" name="action" value="update_status">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="margin: 0;">Contacts enregistrés</h3>
                <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">Enregistrer la sélection</button>
            </div>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background: #f1f1f1; text-align: left;">
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; width: 40px; text-align: center;">Actif</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Nom</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Prénom</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">E-mail</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contacts)): ?>
                        <tr>
                            <td colspan="5" style="padding: 15px; text-align: center; color: #777;">Aucun contact dans le répertoire.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">
                                    <input type="checkbox" name="active_contacts[]" value="<?= htmlspecialchars($contact['id']) ?>" <?= ($contact['active'] ?? true) ? 'checked' : '' ?> style="width: 16px; height: 16px; cursor: pointer;">
                                </td>
                                <td style="padding: 10px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($contact['nom']) ?></td>
                                <td style="padding: 10px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($contact['prenom']) ?></td>
                                <td style="padding: 10px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($contact['email']) ?></td>
                                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right; white-space: nowrap;">
                                    <button type="button" class="btn btn-secondary" style="background: #f0ad4e; color: #fff; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px; margin-right: 5px;" onclick="openEditModal('<?= $contact['id'] ?>', '<?= htmlspecialchars($contact['nom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($contact['prenom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($contact['email'], ENT_QUOTES) ?>')">Modifier</button>
                                    <button type="submit" form="delete-form-<?= $contact['id'] ?>" class="btn btn-secondary" style="background: #d9534f; color: #fff; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px;" onclick="return confirm('Supprimer ce contact ?');">Supprimer</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>

        <!-- Formulaires de suppression isolés -->
        <?php foreach ($contacts as $contact): ?>
            <form id="delete-form-<?= $contact['id'] ?>" method="POST" style="display: none;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="contact_id" value="<?= htmlspecialchars($contact['id']) ?>">
            </form>
        <?php endforeach; ?>
    </div>

    <!-- Modale de modification -->
    <div id="edit-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
        <div style="background: #fff; padding: 30px; border-radius: 6px; width: 400px; max-width: 90%;">
            <h3 style="margin-top: 0; margin-bottom: 15px;">Modifier le contact</h3>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit-contact-id" name="contact_id">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="edit-nom">Nom :</label>
                    <input type="text" id="edit-nom" name="nom" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="edit-prenom">Prénom :</label>
                    <input type="text" id="edit-prenom" name="prenom" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="edit-email">Adresse e-mail :</label>
                    <input type="email" id="edit-email" name="email" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, nom, prenom, email) {
            document.getElementById('edit-contact-id').value = id;
            document.getElementById('edit-nom').value = nom;
            document.getElementById('edit-prenom').value = prenom;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-modal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('edit-modal').style.display = 'none';
        }
    </script>
</body>
</html>