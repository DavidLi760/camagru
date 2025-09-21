<?php
if (!isset($_GET['file'])) {
    echo "Aucune photo sélectionnée.";
    exit;
}

$filename = basename($_GET['file']);
$filepath = "uploads/$filename";

if (!file_exists($filepath)) {
    echo "Photo introuvable.";
    exit;
}

// Ici tu pourrais récupérer les commentaires et likes depuis ta base de données
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Camagru - Photo</title>
</head>
<body>
    <h1>Photo</h1>
    <img src="<?php echo $filepath; ?>" style="max-width:600px; display:block; margin-bottom:20px;">

    <!-- Ici : likes et commentaires -->
    <div id="likes-comments">
        <p>👍 Likes : ...</p>
        <p>💬 Commentaires : ...</p>
    </div>

    <p><a href="gallery.php">Retour à la galerie</a></p>
</body>
</html>

