<?php
require_once __DIR__ . '/../config/database.php';
$message = '';
$showForm = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Vérifier le token
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = :token");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $showForm = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['password'];
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = :pwd, reset_token = NULL WHERE id = :id");
            $stmt->execute([
                ':pwd' => $hashed,
                ':id' => $user['id']
            ]);

            $message = "✅ Votre mot de passe a été réinitialisé.";
            $showForm = false;
        }
    } else {
        $message = "❌ Token invalide ou expiré.";
    }
} else {
    $message = "❌ Aucun token fourni.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation mot de passe</title>
</head>
<body>
    <h1>Réinitialiser le mot de passe</h1>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <?php if ($showForm): ?>
        <form method="POST" action="">
            <input type="password" name="password" placeholder="Nouveau mot de passe" required>
            <button type="submit">Réinitialiser le mot de passe</button>
        </form>
    <?php endif; ?>
</body>
</html>

