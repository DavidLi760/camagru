<?php
$bgColor = "#f0f8ff"; // Couleur de fond
$textColor = "red";    // Couleur du texte
$message = "Bienvenue sur mon site !";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Camagru</title>
    <style>
        body {
            background-color: <?php echo $bgColor; ?>; /* arrière-plan */
            color: <?php echo $textColor; ?>;         /* couleur du texte par défaut */
            font-family: Arial, sans-serif;
        }
        .highlight {
            background-color: yellow; /* arrière-plan d’un élément spécifique */
            color: black;             /* texte sur cet élément */
            padding: 10px;
        }
    </style>
</head> 
<div style="width: 2000px; height: 100px; background-color: lightblue;"></div>
<body>
    <h1><?php echo $message; ?></h1>
    <p class="highlight">Texte avec arrière-plan jaune et texte noir</p>
    <p style="background-color: lightgreen;">Texte avec arrière-plan vert clair directement dans la balise</p>
</body>
</html>
