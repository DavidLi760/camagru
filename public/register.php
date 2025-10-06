<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Vérification du mot de passe complexe côté serveur
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
    if (!preg_match($pattern, $password)) {
        $message = "❌ Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
    } else {
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
    
    <!-- Mot de passe avec message d’avertissement JS -->
    <input type="password" name="password" id="password" placeholder="Mot de passe" required>
    <small id="pwd-msg" style="color:red;"></small>
    
    <button type="submit">S'inscrire</button>
</form>

<?php include 'footer.php'; ?>

<script>
// Vérification mot de passe côté client
const pwdInput = document.getElementById('password');
const pwdMsg = document.getElementById('pwd-msg');

pwdInput.addEventListener('input', () => {
    const pwd = pwdInput.value;
    const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    if (!pattern.test(pwd)) {
        pwdMsg.textContent = "Mot de passe trop faible !";
    } else {
        pwdMsg.textContent = "";
    }
});
</script>
</body>
</html>
