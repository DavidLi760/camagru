<?php
require_once __DIR__ . '/../config/database.php'; // adapte le chemin selon ton arborescence

$email = 'linebulios2003@gmail.com'; // l'email à supprimer

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);

    echo "✅ Utilisateur supprimé : $email";
} catch (PDOException $e) {
    die("❌ Erreur : " . $e->getMessage());
}
