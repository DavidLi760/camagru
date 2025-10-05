<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        $message = "❌ Cet email est déjà utilisé.";
    } else {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(16)); // token unique de 32 caractères

            // Insérer l'utilisateur mais non confirmé
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, confirmation_token, is_confirmed) 
                VALUES (:username, :email, :password, :token, 0)
            ");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword,
                ':token' => $token
            ]);

            // Préparer le mail de confirmation
            $confirmationLink = "http://localhost:8080/confirm.php?token=$token";
            $subject = "Confirmez votre compte";
            $body = "Bonjour $username,\n\nMerci de vous inscrire ! Veuillez confirmer votre compte en cliquant sur le lien suivant :\n$confirmationLink";

            $headers = "From: noreply@ton-domaine.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            if (mail($email, $subject, $body, $headers)) {
                $message = "✅ Inscription réussie ! Vérifiez votre email pour confirmer votre compte.";
            } else {
                $message = "❌ Impossible d'envoyer l'email de confirmation.";
            }

        } catch (PDOException $e) {
            $message = "❌ Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <?php include 'header.php'; ?>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>
    <h1>Créer un compte</h1>

    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>
