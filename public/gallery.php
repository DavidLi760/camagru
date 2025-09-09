<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Camagru - Galerie</title>
</head>
<body>
    <h1>Galerie</h1>

    <?php
    $files = glob("uploads/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
    foreach ($files as $file) {
        echo "<img src='$file' style='max-width:200px; margin:10px;'>";
    }
    ?>

    <a href="index.php">Retour à l'accueil</a>
</body>
</html>