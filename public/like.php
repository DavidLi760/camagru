<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
    exit;
}

$imageId = $_POST['image_id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$imageId) {
    echo json_encode(['success' => false, 'message' => 'Image non spécifiée']);
    exit;
}

// Vérifier si l'utilisateur a déjà liké
$stmt = $pdo->prepare("SELECT * FROM likes WHERE user_id = :uid AND image_id = :iid");
$stmt->execute([':uid' => $userId, ':iid' => $imageId]);
$alreadyLiked = $stmt->fetch();

if ($alreadyLiked) {
    // Supprimer le like
    $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = :uid AND image_id = :iid");
    $stmt->execute([':uid' => $userId, ':iid' => $imageId]);
    $action = 'unliked';
} else {
    // Ajouter le like
    $stmt = $pdo->prepare("INSERT INTO likes (user_id, image_id) VALUES (:uid, :iid)");
    $stmt->execute([':uid' => $userId, ':iid' => $imageId]);
    $action = 'liked';
}

// Compter les likes à jour
$stmt = $pdo->prepare("SELECT COUNT(*) as like_count FROM likes WHERE image_id = :iid");
$stmt->execute([':iid' => $imageId]);
$likeCount = $stmt->fetch(PDO::FETCH_ASSOC)['like_count'];

echo json_encode(['success' => true, 'likeCount' => $likeCount, 'action' => $action]);
