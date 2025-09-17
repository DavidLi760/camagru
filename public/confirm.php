<?php
require_once __DIR__ . '/../config/database.php';

$message = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        // Vérifier si le token existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE confirmation_token = :token AND is_confirmed = 0");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();

        if ($user) {
            // Confirmer le compte
            $stmt = $pdo->prepare("UPDATE users SET is_confirmed = 1, confirmation_token = NULL WHERE id = :id");
            $stmt->execute([':id' => $user['id']]);

            $message = "✅ Votre compte a été confirmé avec succès ! Vous pouvez maintenant vous connecter.";
        } else {
            $message = "❌ Lien invalide ou compte déjà confirmé.";
        }

    } catch (PDOException $e) {
        $message = "❌ Erreur : " . $e->getMessage();
    }
} else {
    $message = "❌ Aucun token fourni.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation</title>
</head>
<body>
    <h1>Confirmation du compte</h1>
    <p><?php echo $message; ?></p>
    <a href="login.php">Se connecter</a>
</body>
</html>
