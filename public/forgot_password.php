<?php
require_once __DIR__ . '/../config/database.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Vérifier si l'email existe
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Générer un token unique pour réinitialisation
        $token = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare("UPDATE users SET reset_token = :token WHERE id = :id");
        $stmt->execute([
            ':token' => $token,
            ':id' => $user['id']
        ]);

        // Créer le lien de réinitialisation
        $resetLink = "http://localhost:8080/reset_password.php?token=$token";
        $subject = "Réinitialisation de votre mot de passe";
        $body = "Bonjour {$user['username']},\n\n";
        $body .= "Pour réinitialiser votre mot de passe, cliquez sur ce lien :\n$resetLink\n\n";
        $body .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.";

        $headers = "From: noreply@ton-domaine.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (mail($email, $subject, $body, $headers)) {
            $message = "✅ Un email de réinitialisation a été envoyé.";
        } else {
            $message = "❌ Impossible d'envoyer l'email.";
        }
    } else {
        $message = "❌ Aucun compte trouvé avec cet email.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
</head>
<body>
    <h1>Réinitialiser le mot de passe</h1>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Votre email" required>
        <button type="submit">Envoyer le lien de réinitialisation</button>
    </form>
</body>
</html>
