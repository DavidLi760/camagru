<?php
session_start();
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
        .gallery-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 10px;
            min-height: calc(2 * 370px + 10px); /* force la hauteur pour 2 lignes */
        }

        .gallery-img {
            width: 20vw;       /* largeur fixe */
            height: 370px;      /* hauteur fixe */
            object-fit: cover;  /* recadre l'image pour remplir le carré */
            margin: 10px;
            border-radius: 8px; /* coins arrondis */
        }

        .placeholder {
            visibility: hidden; /* garde l'espace mais ne s'affiche pas */
        }

        .pagination-container {
            margin-top: 20px;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            align-items: center;
        }

        /* Style des boutons de pagination */
        .pagination-container a,
        .pagination-container strong {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            margin: 5px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            background-color: #eee;
            color: black;
        }

        .pagination-container strong {
            background-color: #333;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Galerie</h1>

    <div class="gallery-container">
        <?php if (empty($images)): ?>
            <p>Aucune image publiée pour le moment.</p>
        <?php else: ?>
            <?php foreach ($images as $img): ?>
                <?php $filename = htmlspecialchars(basename($img['image_path'])); ?>
                <a href="photo.php?file=<?php echo $filename; ?>">
                    <img src="<?php echo htmlspecialchars($img['image_path']); ?>" class="gallery-img">
                </a>
            <?php endforeach; ?>

            <!-- Images fantômes pour garder la place de 8 images -->
            <?php
            $imagesDisplayed = count($images);
            $placeholders = max(0, 8 - $imagesDisplayed);
            for ($i = 0; $i < $placeholders; $i++): ?>
                <div class="gallery-img placeholder"></div>
            <?php endfor; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
    <!-- Flèche précédent -->
    <?php if ($page > 0): ?>
        <a href="index.php?page=<?php echo $page - 1; ?>">&laquo;</a>
    <?php endif; ?>

    <!-- Pages (ex: affichage limité à 9) -->
    <?php
    $start = max(1, $page - 4);
    $end = min($totalPages, $page + 4);

    if ($start > 1) {
        echo '<a href="index.php?page=1">1</a>';
        if ($start > 2) echo '<span>...</span>';
    }

    for ($i = $start; $i <= $end; $i++):
        if ($i == $page): ?>
            <strong><?php echo $i; ?></strong>
        <?php else: ?>
            <a href="index.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endif;
    endfor;

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) echo '<span>...</span>';
        echo '<a href="index.php?page='.$totalPages.'">'.$totalPages.'</a>';
    }
    ?>

    <!-- Flèche suivant -->
    <?php if ($page < $totalPages): ?>
        <a href="index.php?page=<?php echo $page + 1; ?>">&raquo;</a>
    <?php else :?>
        <a href="index.php?page=<?php echo $page + 0; ?>">&raquo;</a>
    <?php endif; ?>
</div>

    <?php endif; ?>
<?php include 'footer.php'; ?>
</body>
</html>
