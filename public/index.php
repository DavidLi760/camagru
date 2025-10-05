<?php
require_once "pdo.php"; // connexion à la base

$imagesPerPage = 8; // nombre d'images par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $imagesPerPage;

// Récupérer les images pour la page courante
$stmt = $pdo->prepare("SELECT * FROM images ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $imagesPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre total d'images pour calculer le nombre de pages
$stmt = $pdo->query("SELECT COUNT(*) FROM images");
$totalImages = $stmt->fetchColumn();
$totalPages = ceil($totalImages / $imagesPerPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'header.php'; ?>
    <meta charset="UTF-8">
    <title>Camagru</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <style>
        .gallery-img {
            width: 20vw;       /* largeur fixe */
            height: 370px;      /* hauteur fixe */
            object-fit: cover;  /* recadre l'image pour remplir le carré */
            margin: 10px;
            border-radius: 8px; /* optionnel, coins arrondis */
        }
    </style>
</head>
<body>
    <h1>Galerie</h1>

    <?php if (empty($images)): ?>
        <p>Aucune image publiée pour le moment.</p>
    <?php else: ?>
        <?php foreach ($images as $img): ?>
            <?php $filename = htmlspecialchars(basename($img['image_path'])); ?>
            <a href="photo.php?file=<?php echo $filename; ?>">
                <img src="<?php echo htmlspecialchars($img['image_path']); ?>" class="gallery-img">
            </a>

        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="margin-top:20px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?>
                    <strong><?php echo $i; ?></strong>
                <?php else: ?>
                    <a href="index.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</body>
</html>
