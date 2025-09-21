<?php
session_start();
require_once "pdo.php"; // connexion BDD

if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour uploader une image.");
}

$uploadDir = __DIR__ . "/uploads/"; // chemin serveur
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$stickersDir = __DIR__ . "/stickers/"; // dossier stickers

$successMsg = "";
$imgPathRel = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {

    $tmpFile = $_FILES['photo']['tmp_name'];
    $stickerName = $_POST['sticker'] ?? '';
    $stickerPath = $stickersDir . basename($stickerName);

    if (!file_exists($stickerPath)) die("❌ Sticker introuvable");

    // Charger image source
    $src = imagecreatefromstring(file_get_contents($tmpFile));
    imagesavealpha($src, true);
    imagealphablending($src, true);

    // Charger sticker
    $sticker = imagecreatefrompng($stickerPath);
    imagesavealpha($sticker, true);
    imagealphablending($sticker, true);

    // Redimension sticker (20% largeur)
    $srcW = imagesx($src);
    $srcH = imagesy($src);
    $stickerW = imagesx($sticker);
    $stickerH = imagesy($sticker);

    $newStickerW = (int)($srcW * 0.2);
    $newStickerH = (int)($stickerH * ($newStickerW / $stickerW));

    $stickerResized = imagecreatetruecolor($newStickerW, $newStickerH);
    imagesavealpha($stickerResized, true);
    imagealphablending($stickerResized, false);
    imagecopyresampled($stickerResized, $sticker, 0,0,0,0, $newStickerW, $newStickerH, $stickerW, $stickerH);

    // Position bas-droite
    $x = $srcW - $newStickerW - 10;
    $y = $srcH - $newStickerH - 10;

    imagecopy($src, $stickerResized, $x, $y, 0, 0, $newStickerW, $newStickerH);

    // --- Enregistrement ---
    $filenameRel = "uploads/" . uniqid("img_") . ".png"; // chemin relatif pour HTML/BDD
    $filenameFull = __DIR__ . "/" . $filenameRel; // chemin serveur
    imagepng($src, $filenameFull);

    // Libération mémoire
    imagedestroy($src);
    imagedestroy($sticker);
    imagedestroy($stickerResized);

    // Insertion en BDD
    $stmt = $pdo->prepare("INSERT INTO images (user_id, image_path) VALUES (:uid, :path)");
    $stmt->execute([
        ":uid" => $_SESSION['user_id'],
        ":path" => $filenameRel
    ]);

    $successMsg = "✅ Image générée avec sticker choisi !";
    $imgPathRel = $filenameRel;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Uploader une photo avec sticker</title>
</head>
<body>

<?php if ($successMsg): ?>
    <p><?php echo $successMsg; ?></p>
    <img src="<?php echo htmlspecialchars($imgPathRel); ?>" style="max-width:400px">
<?php endif; ?>

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

