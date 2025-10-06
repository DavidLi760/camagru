<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['file'])) {
    echo "Aucune photo sélectionnée.";
    exit;
}

$filename = basename($_GET['file']);
$filepath = "uploads/$filename";

// Vérifier si le fichier existe
if (!file_exists($filepath)) {
    echo "Photo introuvable.";
    exit;
}

// Récupérer l'image depuis la base pour connaître son ID
$stmt = $pdo->prepare("SELECT * FROM images WHERE image_path = :path");
$stmt->execute([':path' => $filepath]);
$image = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$image) {
    echo "Image introuvable dans la base.";
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
    <link rel="stylesheet" href="style.css">
    <?php include 'header.php'; ?>
    <meta charset="UTF-8">
    <title>Camagru - Photo</title>
</head>
<body>
    <h1>Photo</h1>
    <img src="<?php echo $filepath; ?>" style="height:1000; width:700; display:block; margin-bottom:20px;">

    <!-- Likes -->
<div style="display: flex; align-items: center; gap: 10px;">
    <p style="margin: 0; font-size: 1.5em;">
        👍 Likes : <span id="like-count"><?php echo $likeCount; ?></span>
    </p>

    <?php if (isset($_SESSION['user_id'])): ?>
        <button id="like-btn" data-id="<?php echo $imageId; ?>" style="font-size: 1.5em; cursor:pointer;">
            👍
        </button>
    <?php endif; ?>
</div>







    <!-- Commentaires -->
    <h3>Commentaires</h3>
    <?php foreach ($comments as $c): ?>
        <div>
            <strong><?php echo htmlspecialchars($c['username']); ?></strong> :
            <?php echo htmlspecialchars($c['content']); ?>
            <small>(<?php echo $c['created_at']; ?>)</small>
        </div>
    <?php endforeach; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
    <form method="POST" action="comment.php?file=<?php echo urlencode($filename); ?>">
        <input type="hidden" name="image_id" value="<?php echo $imageId; ?>">
        <textarea name="content" placeholder="Votre commentaire..." required></textarea>
        <button type="submit">Publier</button>
    </form>
    <?php endif; ?>
    <!-- ===== Fin des interactions ===== -->

    <p><a href="home">Retour à la galerie</a></p>
<script>
const likeBtn = document.getElementById('like-btn');
if (likeBtn) {
    likeBtn.addEventListener('click', () => {
        const imageId = likeBtn.dataset.id;

        fetch('like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'image_id=' + encodeURIComponent(imageId)
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