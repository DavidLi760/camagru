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
    <link rel="stylesheet" href="style.css">
    <?php include 'header.php'; ?>
    <meta charset="UTF-8">
    <title>Profil de <?php echo htmlspecialchars($username); ?></title>
    <style>
        .gallery-img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            margin: 10px;
            border-radius: 8px;
        }
        .user-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .image-card {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>
</head>
<body>
    <h1>Profile de <?php echo htmlspecialchars($username); ?> : </h1>
    <a href="profile_edit.php">Modifier ses coordonnées</a>
    
    <form method="POST">
        <label>
            <input type="checkbox" name="notify_on_comment" <?php if($user['notify_on_comment']) echo "checked"; ?>>
            Recevoir un email lorsqu'un commentaire est publié sur mes photos
        </label>
        <button type="submit">Enregistrer</button>
    </form>

    <h2>Vos images</h2>
    <?php if (empty($images)): ?>
        <p>Aucune image publiée pour le moment.</p>
    <?php else: ?>
        <div class="user-gallery">
            <?php foreach ($images as $img): ?>
                <?php $filename = htmlspecialchars(basename($img['image_path'])); ?>
                <div class="image-card">
                    <!-- Image cliquable -->
                    <a href="photo.php?file=<?php echo $filename; ?>">
                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" class="gallery-img">
                    </a>
                    <p>Publié le <?php echo htmlspecialchars($img['created_at']); ?></p>
                    <form method="POST" action="delete_image.php">
                        <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                        <button type="submit">🗑️ Supprimer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
