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

            // Vérification côté serveur
            $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
            if (!preg_match($pattern, $newPassword)) {
                $message = "❌ Mot de passe trop faible ! Il doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
            } else {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("UPDATE users SET password = :pwd, reset_token = NULL WHERE id = :id");
                $stmt->execute([
                    ':pwd' => $hashed,
                    ':id' => $user['id']
                ]);

                $message = "✅ Votre mot de passe a été réinitialisé.";
                $showForm = false;
            }
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
    <link rel="stylesheet" href="style.css">
    <?php include 'header.php'; ?>
    <meta charset="UTF-8">
    <title>Réinitialisation mot de passe</title>
</head>
<body>
    <h1>Réinitialiser le mot de passe</h1>
    <?php if ($message) echo "<p>$message</p>"; ?>

    <?php if ($showForm): ?>
        <form method="POST" action="">
            <input type="password" name="password" id="password" placeholder="Nouveau mot de passe" required>
            <small id="pwd-msg" style="color:red;"></small>
            <button type="submit">Réinitialiser le mot de passe</button>
        </form>

        <script>
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
    <?php endif; ?>

<?php include 'footer.php'; ?>
</body>
</html>
