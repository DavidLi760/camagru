<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Camagru - Upload</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
    <h1>Uploader une image</h1>

    <!-- enctype="multipart/form-data" est OBLIGATOIRE pour uploader un fichier -->
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="image" accept="image/*" required>
        <button type="submit" name="submit">Uploader</button>
    </form>

    <a href="index.php">Retour à l'accueil</a>
</body>
</html>

<?php
if (isset($_POST['submit'])) {
    $targetDir = "uploads/"; // dossier où stocker les images
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true); // crée le dossier s'il n'existe pas
    }

    $targetFile = $targetDir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Vérifie si c'est bien une image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        echo "❌ Ce fichier n'est pas une image.";
        exit;
    }

    // Déplace l'image du dossier temporaire vers "uploads/"
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        echo "✅ Image ". htmlspecialchars(basename($_FILES["image"]["name"])) . " uploadée avec succès.";
    } else {
        echo "❌ Erreur pendant l'upload.";
    }
}
?>