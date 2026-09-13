<?php
header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        
        if (in_array($extension, $allowedExtensions)) {
            $filename = 'img_' . uniqid() . '.' . $extension;
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $response['success'] = true;
                $response['path'] = 'uploads/' . $filename;
            } else {
                $response['error'] = "Erreur lors du déplacement du fichier.";
            }
        } else {
            $response['error'] = "Format de fichier non autorisé.";
        }
    } else {
        $response['error'] = "Erreur d'upload code " . $file['error'];
    }
} else {
    $response['error'] = "Aucun fichier reçu.";
}

echo json_encode($response);