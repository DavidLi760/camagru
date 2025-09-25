<?php
session_start();
require_once "pdo.php"; // connexion BDD

if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour uploader une image.");
}

$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$stickersDir = __DIR__ . "/stickers/";

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

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $tmpFile = $_FILES['photo']['tmp_name'];
    } elseif (isset($_POST['photo']) && strpos($_POST['photo'], 'data:image') === 0) {
        $data = $_POST['photo'];
        $data = explode(',', $data)[1]; 
        $tmpFile = tempnam(sys_get_temp_dir(), 'cam_');
        file_put_contents($tmpFile, base64_decode($data));
        $fromWebcam = true;
    } else {
        die("❌ Aucune image reçue");
    }

    $src = @imagecreatefromstring(file_get_contents($tmpFile));
    if (!$src) die("❌ Impossible de charger l'image source.");
    imagesavealpha($src, true);
    imagealphablending($src, true);

    // Redimension sticker
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

    $x = $srcW - $newStickerW - 10;
    $y = $srcH - $newStickerH - 10;
    imagecopy($src, $stickerResized, $x, $y, 0, 0, $newStickerW, $newStickerH);

    $filenameRel = "uploads/" . uniqid("img_") . ".png";
    $filenameFull = __DIR__ . "/" . $filenameRel;
    imagepng($src, $filenameFull);

    imagedestroy($src);
    imagedestroy($sticker);
    imagedestroy($stickerResized);

    if ($fromWebcam) unlink($tmpFile);

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
    <link rel="stylesheet" href="style.css">
    <?php include 'header.php'; ?>
    <meta charset="UTF-8">
    <title>Uploader une photo avec sticker</title>
</head>
<body>

<?php if ($successMsg): ?>
    <p><?php echo $successMsg; ?></p>
    <img src="<?php echo htmlspecialchars($imgPathRel); ?>" style="max-width:400px">
<?php endif; ?>

<h1>Uploader une photo ou utiliser la webcam avec sticker</h1>
<input type="file" id="fileInput" accept="image/*">
<button id="clearFileBtn">❌ Supprimer</button><br><br>

<canvas id="preview" width="400" height="300" style="border:1px solid #ccc;"></canvas><br>

<label for="stickerWebcam">Choisir un sticker :</label>
<select id="stickerWebcam" required>
    <option value="moustache.png">Moustache</option>
    <option value="lunette.png">Lunettes</option>
    <option value="chapeau.png">Chapeau</option>
</select><br><br>

<button id="snapBtn" disabled>Prendre / Uploader la photo</button>
<button id="camToggleBtn">Désactiver caméra</button>

<form method="POST" action="upload.php" enctype="multipart/form-data" id="uploadForm" style="display:none;">
    <input type="hidden" name="photo" id="photoInput">
    <input type="hidden" name="sticker" id="stickerHidden">
</form>

<script>
let stream = null;
const video = document.createElement('video');
video.autoplay = true;

const preview = document.getElementById('preview');
const ctx = preview.getContext('2d');
const snapBtn = document.getElementById('snapBtn');
const stickerSelect = document.getElementById('stickerWebcam');
const photoInput = document.getElementById('photoInput');
const stickerHidden = document.getElementById('stickerHidden');
const fileInput = document.getElementById('fileInput');
const clearFileBtn = document.getElementById('clearFileBtn');
const camToggleBtn = document.getElementById('camToggleBtn');

let stickerImg = new Image();
stickerSelect.addEventListener('change', () => {
    stickerImg.src = 'stickers/' + stickerSelect.value;
    snapBtn.disabled = !stickerImg.src || (!fileInput.files.length && !stream);
});

// Gestion caméra
function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: true })
    .then(s => { stream = s; video.srcObject = stream; camToggleBtn.textContent = "Désactiver caméra"; snapBtn.disabled = false; })
    .catch(err => console.error("Webcam inaccessible", err));
}

function stopCamera() {
    if (stream) { stream.getTracks().forEach(track => track.stop()); stream = null; camToggleBtn.textContent = "Activer caméra"; snapBtn.disabled = fileInput.files.length === 0; }
}

camToggleBtn.addEventListener('click', (e) => { e.preventDefault(); if(stream) stopCamera(); else startCamera(); });

// Lecture du fichier uploadé
let uploadedImg = null;
fileInput.addEventListener('change', () => {
    if (!fileInput.files.length) { uploadedImg = null; snapBtn.disabled = !stream; return; }
    const reader = new FileReader();
    reader.onload = (e) => {
        uploadedImg = new Image();
        uploadedImg.src = e.target.result;
        uploadedImg.onload = () => { snapBtn.disabled = stickerImg.src === '' ? true : false; };
    };
    reader.readAsDataURL(fileInput.files[0]);
});

// Supprimer l'image uploadée
clearFileBtn.addEventListener('click', (e) => {
    e.preventDefault();
    fileInput.value = "";
    uploadedImg = null;
    snapBtn.disabled = !stream;
});

// Dessin en boucle
function drawLoop() {
    const canvasW = preview.width;
    const canvasH = preview.height;

    // Remplir en noir
    ctx.fillStyle = "black";
    ctx.fillRect(0, 0, canvasW, canvasH);

    let drawW, drawH, offsetX, offsetY;

    if (uploadedImg) {
        const ratio = Math.min(canvasW / uploadedImg.width, canvasH / uploadedImg.height);
        drawW = uploadedImg.width * ratio;
        drawH = uploadedImg.height * ratio;
        offsetX = (canvasW - drawW) / 2;
        offsetY = (canvasH - drawH) / 2;
        ctx.drawImage(uploadedImg, offsetX, offsetY, drawW, drawH);
    } else if (stream && video.videoWidth && video.videoHeight) {
        const ratio = Math.min(canvasW / video.videoWidth, canvasH / video.videoHeight);
        drawW = video.videoWidth * ratio;
        drawH = video.videoHeight * ratio;
        offsetX = (canvasW - drawW) / 2;
        offsetY = (canvasH - drawH) / 2;
        ctx.drawImage(video, offsetX, offsetY, drawW, drawH);
    }

    // Sticker en bas à droite
    if (stickerImg.complete) {
        const sw = canvasW * 0.2;
        const sh = stickerImg.height * (sw / stickerImg.width);
        ctx.drawImage(stickerImg, canvasW - sw - 10, canvasH - sh - 10, sw, sh);
    }

    requestAnimationFrame(drawLoop);
}


drawLoop();

// Capture / upload
snapBtn.addEventListener('click', (e)=>{
    e.preventDefault();
    photoInput.value = preview.toDataURL('image/png');
    stickerHidden.value = stickerSelect.value;
    document.getElementById('uploadForm').submit();
});

// Démarrage initial caméra
startCamera();
</script>
</body>
</html>