<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Générer un token CSRF si inexistant
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Vérification du paramètre "file"
if (!isset($_GET['file'])) {
    header("Location: index.php?error=no_photo");
    exit;
}

$filename = basename($_GET['file']);
$filepath = __DIR__ . "/uploads/$filename";

// Vérifier si le fichier existe et est bien une image
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!file_exists($filepath) || !in_array($ext, $allowed_extensions)) {
    header("Location: index.php?error=no_photo");
    exit;
}

// Récupérer l'image depuis la base
$stmt = $pdo->prepare("SELECT * FROM images WHERE image_path = :path");
$stmt->execute([':path' => "uploads/$filename"]); // Stockage relatif
$image = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$image) {
    header("Location: index.php?error=no_photo");
    exit;
}

$imageId = $image['id'];

// Récupérer les likes
$stmt = $pdo->prepare("SELECT COUNT(*) as like_count FROM likes WHERE image_id = :id");
$stmt->execute([':id' => $imageId]);
$likeCount = $stmt->fetch(PDO::FETCH_ASSOC)['like_count'];

// Récupérer les commentaires
$stmt = $pdo->prepare("
    SELECT c.content, u.username, c.created_at 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.image_id = :id
    ORDER BY c.created_at DESC
");
$stmt->execute([':id' => $imageId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Camagru - Photo</title>
    <link rel="stylesheet" href="style.css">
    <?php include 'header.php'; ?>
</head>
<body>
    <h1>Photo</h1>

    <img src="<?= htmlspecialchars("uploads/$filename") ?>" alt="Photo Camagru" style="max-width:700px; height:auto; display:block; margin-bottom:20px;">

    <!-- Likes -->
    <div style="display: flex; align-items: center; gap: 10px;">
        <p style="margin: 0; font-size: 1.5em;">
            👍 Likes : <span id="like-count"><?= $likeCount ?></span>
        </p>

        <?php if (isset($_SESSION['user_id'])): ?>
            <button id="like-btn" data-id="<?= $imageId ?>" data-csrf="<?= $_SESSION['csrf_token'] ?>" style="font-size: 1.5em; cursor:pointer;">
                👍
            </button>
        <?php endif; ?>
    </div>

    <!-- Commentaires -->
    <h3>Commentaires</h3>
    <?php foreach ($comments as $c): ?>
        <div>
            <strong><?= htmlspecialchars($c['username']) ?></strong> :
            <?= htmlspecialchars($c['content']) ?>
            <small>(<?= htmlspecialchars($c['created_at']) ?>)</small>
        </div>
    <?php endforeach; ?>

    <!-- Formulaire commentaire -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <form method="POST" action="comment.php">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="image_id" value="<?= $imageId ?>">
        <textarea name="content" placeholder="Votre commentaire..." required></textarea>
        <button type="submit">Publier</button>
    </form>
    <?php endif; ?>

    <p><a href="home">Retour à la galerie</a></p>

<script>
const likeBtn = document.getElementById('like-btn');
if (likeBtn) {
    likeBtn.addEventListener('click', () => {
        const imageId = likeBtn.dataset.id;
        const csrfToken = likeBtn.dataset.csrf;

        fetch('like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'image_id=' + encodeURIComponent(imageId) + '&csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('like-count').textContent = data.likeCount;
            } else {
                alert(data.message || 'Erreur lors du like');
            }
        })
        .catch(err => console.error('Erreur AJAX:', err));
    });
}
</script>

<?php include 'footer.php'; ?>
</body>
</html>
