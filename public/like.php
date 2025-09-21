<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) exit;
if (!isset($_POST['image_id'])) exit;

$userId = $_SESSION['user_id'];
$imageId = $_POST['image_id'];

// Insérer le like (UNIQUE grâce à la contrainte SQL)
try {
    $stmt = $pdo->prepare("INSERT INTO likes (user_id, image_id) VALUES (:uid, :iid)");
    $stmt->execute([':uid' => $userId, ':iid' => $imageId]);
} catch (PDOException $e) {
    // Si l'utilisateur a déjà liké, ignorer l'erreur
}

header("Location: photo.php?file=" . urlencode($_GET['file']));
