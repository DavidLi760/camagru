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

    // Vérifier si l'email est déjà utilisé par un autre utilisateur
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $stmt->execute([':email' => $newEmail, ':id' => $userId]);
    if ($stmt->fetch()) {
        $message = "❌ Cet email est déjà utilisé par un autre utilisateur.";
    } else {
        try {
            $params = [
                ':username' => $newUsername,
                ':email'    => $newEmail,
                ':id'       => $userId
            ];

            $sql = "UPDATE users SET username = :username, email = :email";

            // Si l'utilisateur a saisi un nouveau mot de passe
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql .= ", password = :password";
                $params[':password'] = $hashedPassword;
            }

            $sql .= " WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $message = "✅ Profil mis à jour avec succès !";

            // Mettre à jour le nom dans la session
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
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <input type="password" name="password" placeholder="Nouveau mot de passe (laisser vide pour ne pas changer)">
        <button type="submit">Mettre à jour</button>
    <a href="profile.php">Retour au profil</a>
<?php include 'footer.php'; ?>
</body>
</html>
