<?php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id']) || !isset($_POST['image_id'])) {
    header("Location: profile.php");
    exit;
}

$userId  = $_SESSION['user_id'];
$imageId = (int) $_POST['image_id'];

// Récupérer le chemin du fichier et vérifier que l'image appartient bien à l'utilisateur
$stmt = $pdo->prepare("SELECT image_path FROM images WHERE id = :id AND user_id = :uid");
$stmt->execute([':id' => $imageId, ':uid' => $userId]);
$image = $stmt->fetch(PDO::FETCH_ASSOC);

if ($image) {
    $filePath = $image['image_path'];

    // 1️⃣ Supprimer le fichier physique
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // 2️⃣ Supprimer l'entrée de la DB
    $stmt = $pdo->prepare("DELETE FROM images WHERE id = :id AND user_id = :uid");
    $stmt->execute([':id' => $imageId, ':uid' => $userId]);
}

// Retour au profil
header("Location: profile.php");
exit;
