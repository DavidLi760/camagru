<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once "pdo.php"; // connexion à la base

$username = $_SESSION['username'];
$userId   = $_SESSION['user_id'];

// Récupérer les images de l’utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM images WHERE user_id = :uid ORDER BY created_at DESC");
$stmt->execute([":uid" => $userId]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <h2>Vos images</h2>
    <?php if (empty($images)): ?>
        <p>Aucune image publiée pour le moment.</p>
    <?php else: ?>
        <?php foreach ($images as $img): ?>
            <div style="margin-bottom:15px;">
                <img src="<?php echo htmlspecialchars($img['image_path']); ?>" style="max-width:200px;">
                <p>Publié le <?php echo htmlspecialchars($img['created_at']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
