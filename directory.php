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
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $action = $_POST['action'] ?? 'add';

    if ($action === 'add') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse e-mail invalide.';
        } else {
            $contacts[] = [
                'id' => 'contact_' . uniqid(),
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email
            ];
            file_put_contents($dataFile, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = 'Contact ajouté avec succès.';
        }
    } elseif ($action === 'delete') {
        $contactId = $_POST['contact_id'] ?? '';
        $contacts = array_filter($contacts, function($c) use ($contactId) {
            return $c['id'] !== $contactId;
        });
        $contacts = array_values($contacts);
        file_put_contents($dataFile, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $success = 'Contact supprimé avec succès.';
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

        <h3>Contacts enregistrés</h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr style="background: #f1f1f1; text-align: left;">
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;">Nom</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;">Prénom</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;">E-mail</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr>
                        <td colspan="4" style="padding: 15px; text-align: center; color: #777;">Aucun contact dans le répertoire.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($contact['nom']) ?></td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($contact['prenom']) ?></td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($contact['email']) ?></td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="contact_id" value="<?= htmlspecialchars($contact['id']) ?>">
                                    <button type="submit" class="btn btn-secondary" style="background: #d9534f; color: #fff; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px;" onclick="return confirm('Supprimer ce contact ?');">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>