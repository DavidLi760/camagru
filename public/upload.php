<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Uploader une photo avec sticker</title>
</head>
<body>
  <h1>Uploader une photo + sticker</h1>
  <form method="POST" action="upload.php" enctype="multipart/form-data">
    <input type="file" name="photo" accept="image/*" required><br><br>

    <label for="sticker">Choisir un sticker :</label>
    <select name="sticker" id="sticker" required>
      <option value="moustache.png">Moustache</option>
      <option value="lunette.png">Lunettes</option>
      <option value="chapeau.png">Chapeau</option>
    </select><br><br>

    <button type="submit">Envoyer</button>
  </form>
</body>
<body>
  <h1>Prendre une photo</h1>

  <video id="video" autoplay width="400"></video>
  <canvas id="canvas" style="display:none;"></canvas>
  <form method="POST" action="upload.php" enctype="multipart/form-data" id="uploadForm">
    <input type="hidden" name="photo" id="photoInput">
    <button id="snapBtn" disabled>Prendre la photo</button>
  </form>

  <script src="js/webcam.js"></script>
</body>
</html>


<?php
$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$stickersDir = __DIR__ . "/stickers/";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    // Fichier uploadé
    $tmpFile = $_FILES['photo']['tmp_name'];

    // Vérifier le sticker choisi
    $stickerName = $_POST['sticker'] ?? '';
    $stickerPath = $stickersDir . basename($stickerName);
    if (!file_exists($stickerPath)) die("❌ Sticker introuvable");

    // Charger l'image source
    $src = imagecreatefromstring(file_get_contents($tmpFile));
    imagesavealpha($src, true);
    imagealphablending($src, true);

    // Charger le sticker
    $sticker = imagecreatefrompng($stickerPath);
    imagesavealpha($sticker, true);
    imagealphablending($sticker, true);

    // Dimensions
    $srcW = imagesx($src);
    $srcH = imagesy($src);
    $stickerW = imagesx($sticker);
    $stickerH = imagesy($sticker);

    // Redimensionner le sticker (ex: 20% de la largeur de la photo)
    $newStickerW = (int)($srcW * 0.2);
    $newStickerH = (int)($stickerH * ($newStickerW / $stickerW));
    $stickerResized = imagecreatetruecolor($newStickerW, $newStickerH);
    imagesavealpha($stickerResized, true);
    imagealphablending($stickerResized, false);
    imagecopyresampled($stickerResized, $sticker, 0, 0, 0, 0, $newStickerW, $newStickerH, $stickerW, $stickerH);

    // Position (bas-droite)
    $x = $srcW - $newStickerW - 10;
    $y = $srcH - $newStickerH - 10;

    // Superposer le sticker
    imagecopy($src, $stickerResized, $x, $y, 0, 0, $newStickerW, $newStickerH);

    // Sauvegarder en PNG
    $filename = $uploadDir . uniqid("img_") . ".png";
    imagepng($src, $filename);

    // Libération mémoire
    imagedestroy($src);
    imagedestroy($sticker);
    imagedestroy($stickerResized);

    echo "✅ Image générée avec sticker choisi !<br>";
    echo "<img src='uploads/" . basename($filename) . "' style='max-width:400px'>";
} else {
    echo "❌ Aucun fichier reçu.";
}

