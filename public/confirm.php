<?php
require_once __DIR__ . '/../config/database.php';

$token = $_GET['token'] ?? '';

if ($token) {
    $stmt = $pdo->prepare("UPDATE users SET is_confirmed = 1, confirmation_token = NULL WHERE confirmation_token = :token");
    $stmt->execute([':token' => $token]);

    if ($stmt->rowCount()) {
        echo "✅ Compte confirmé ! Vous pouvez maintenant vous connecter.";
    } else {
        echo "❌ Token invalide ou compte déjà confirmé.";
    }
} else {
    echo "❌ Aucun token fourni.";
}
