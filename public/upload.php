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

// --- Gestion du formulaire ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stickerName = $_POST['sticker'] ?? '';
    $stickerPath = $stickersDir . basename($stickerName);

    if (!file_exists($stickerPath)) die("❌ Sticker introuvable : $stickerPath");

    $sticker = @imagecreatefrompng($stickerPath);
    if (!$sticker) die("❌ Impossible de charger le sticker. Vérifie qu'il est au format PNG.");
    imagesavealpha($sticker, true);
    imagealphablending($sticker, true);

    // --- Gestion upload ou webcam ---
    $tmpFile = null;
    $fromWebcam = false;

    // 1️⃣ Si fichier uploadé
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $tmpFile = $_FILES['photo']['tmp_name'];
    }
    // 2️⃣ Si photo prise par webcam (base64)
    elseif (isset($_POST['photo']) && strpos($_POST['photo'], 'data:image') === 0) {
        $data = $_POST['photo'];
        $data = explode(',', $data)[1]; // retirer "data:image/png;base64,"
        $tmpFile = tempnam(sys_get_temp_dir(), 'cam_');
        file_put_contents($tmpFile, base64_decode($data));
        $fromWebcam = true;
    } else {
        die("❌ Aucune image reçue");
    }

    // --- Charger image source ---
    $src = @imagecreatefromstring(file_get_contents($tmpFile));
    if (!$src) die("❌ Impossible de charger l'image source.");
    imagesavealpha($src, true);
    imagealphablending($src, true);

    // --- Redimension sticker (20% largeur) ---
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
    $filenameRel = "uploads/" . uniqid("img_") . ".png";
    $filenameFull = __DIR__ . "/" . $filenameRel;
    imagepng($src, $filenameFull);

    // Libération mémoire
    imagedestroy($src);
    imagedestroy($sticker);
    imagedestroy($stickerResized);

    // Supprimer fichier temporaire webcam
    if ($fromWebcam) unlink($tmpFile);

    // --- Insertion en BDD ---
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
    <input type="file" name="photo" accept="image/*"><br><br>

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
    <select name="sticker" id="stickerWebcam" required>
        <option value="moustache.png">Moustache</option>
        <option value="lunette.png">Lunettes</option>
        <option value="chapeau.png">Chapeau</option>
    </select><br><br>
    <button id="snapBtn" disabled>Prendre la photo</button>
</form>

<script>
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const snapBtn = document.getElementById('snapBtn');
const photoInput = document.getElementById('photoInput');
const stickerSelect = document.getElementById('stickerWebcam');

// Accéder à la webcam
navigator.mediaDevices.getUserMedia({ video: true })
  .then(stream => { video.srcObject = stream; })
  .catch(err => { console.error("Webcam inaccessible", err); });

// Activer le bouton dès qu'un sticker est choisi
stickerSelect.addEventListener('change', () => { snapBtn.disabled = false; });

// Capture photo
snapBtn.addEventListener('click', (e) => {
  e.preventDefault();
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0);

  // Convertir en base64
  photoInput.value = canvas.toDataURL('image/png');

  // Copier sticker sélectionné
  document.getElementById('stickerWebcam').name = 'sticker';

  // Soumettre le formulaire
  document.getElementById('uploadForm').submit();
});
</script>
</body>
</html>
