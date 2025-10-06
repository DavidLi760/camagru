<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once "pdo.php"; // connexion à la base

$message = '';
$userId = $_SESSION['user_id'];

// Récupérer les infos actuelles de l'utilisateur
$stmt = $pdo->prepare("SELECT username, email, notify_on_comment FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur introuvable !");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);
    $newEmail    = trim($_POST['email']);
    $newPassword = $_POST['password'];

    $message = '';

    // Vérifier si l'email est renseigné et déjà utilisé
    if (!empty($newEmail)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $stmt->execute([':email' => $newEmail, ':id' => $userId]);
        if ($stmt->fetch()) {
            $message = "❌ Cet email est déjà utilisé par un autre utilisateur.";
        }
    }

    // Vérification de la complexité du mot de passe
    $passwordErrors = [];
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 8) $passwordErrors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        if (!preg_match('/[A-Z]/', $newPassword)) $passwordErrors[] = "Le mot de passe doit contenir au moins une lettre majuscule.";
        if (!preg_match('/[a-z]/', $newPassword)) $passwordErrors[] = "Le mot de passe doit contenir au moins une lettre minuscule.";
        if (!preg_match('/[0-9]/', $newPassword)) $passwordErrors[] = "Le mot de passe doit contenir au moins un chiffre.";
        if (!preg_match('/[\W_]/', $newPassword)) $passwordErrors[] = "Le mot de passe doit contenir au moins un caractère spécial.";
    }

    if (!empty($passwordErrors)) {
        $message = "❌ Erreurs dans le mot de passe :<br>" . implode("<br>", $passwordErrors);
    }

    // Si pas d'erreur → mise à jour
    if (empty($message)) {
        try {
            $params = [':username' => $newUsername, ':id' => $userId];
            $sql = "UPDATE users SET username = :username";

            if (!empty($newEmail)) {
                $sql .= ", email = :email";
                $params[':email'] = $newEmail;
            }

            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql .= ", password = :password";
                $params[':password'] = $hashedPassword;
            }

            $sql .= " WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $message = "✅ Profil mis à jour avec succès !";
            $_SESSION['username'] = $newUsername;

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
    <title>Modifier le profil</title>
</head>
<body>
    <h1>Modifier votre profil</h1>

    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="username" placeholder="Nom d'utilisateur" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <input type="email" name="email" placeholder="Email">
        <input type="password" name="password" placeholder="Nouveau mot de passe (laisser vide pour ne pas changer)">
        <button type="submit">Mettre à jour</button>
    <a href="profile.php">Retour au profil</a>
</body>
</html>
