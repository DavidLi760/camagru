<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once "pdo.php"; // connexion à la base

$username = $_SESSION['username'];
$userId   = $_SESSION['user_id'];

// 1️⃣ Si le formulaire est soumis, mettre à jour notify_on_comment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notify = isset($_POST['notify_on_comment']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE users SET notify_on_comment = :notify WHERE id = :id");
    $stmt->execute([
        ':notify' => $notify,
        ':id' => $userId
    ]);

    // 🔹 Mettre à jour $user pour refléter le changement immédiatement
    $user['notify_on_comment'] = $notify;
}

// 2️⃣ Récupérer les infos de l'utilisateur
$stmt = $pdo->prepare("SELECT username, email, notify_on_comment FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 3️⃣ Récupérer les images de l’utilisateur connecté
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
    <a href="profile_edit.php">Modifier ses coordonnees</a>
    <p>Ceci est votre profil.</p>
    
    </form>
    <form method="POST">
    <label>
        <input type="checkbox" name="notify_on_comment" <?php if($user['notify_on_comment']) echo "checked"; ?>>
        Recevoir un email lorsqu'un commentaire est publié sur mes photos
    </label>
    <button type="submit">Enregistrer</button>
    </form>

    <a href="logout.php">Se déconnecter</a>

    <h2>Vos images</h2>
    <?php if (empty($images)): ?>
        <p>Aucune image publiée pour le moment.</p>
    <?php else: ?>
        <?php foreach ($images as $img): ?>
            <div style="margin-bottom:15px;">
                <img src="<?php echo htmlspecialchars($img['image_path']); ?>" style="max-width:200px;">
                <p>Publié le <?php echo htmlspecialchars($img['created_at']); ?></p>
                <form method="POST" action="delete_image.php" style="margin-top:5px;">
                    <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                    <button type="submit">🗑️ Supprimer</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
