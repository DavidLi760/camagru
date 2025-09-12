<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil de <?php echo htmlspecialchars($username); ?></title>
</head>
<body>
    <h1>Bienvenue, <?php echo htmlspecialchars($username); ?> !</h1>
    <p>Ceci est votre profil.</p>
    <a href="logout.php">Se déconnecter</a>
</body>
</html>
