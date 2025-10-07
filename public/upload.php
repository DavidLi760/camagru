<?php
session_start();
require_once "pdo.php"; // connexion BDD

if (!isset($_SESSION['user_id'])) {
    die("❌ Vous devez être connecté pour uploader une image.");
}

$stmt = $pdo->prepare("DELETE FROM images_upload WHERE user_id = :uid");
$stmt->execute([':uid' => $_SESSION['user_id']]);

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
    $stmt = $pdo->prepare("INSERT INTO images_upload (user_id, image_path) VALUES (:uid, :path)");
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

<h1>Uploader une photo ou utiliser la webcam avec sticker</h1>

<div style="display: flex; gap: 20px; align-items: flex-start;">
    <!-- Galerie scrollable -->
    <div id="gallery" style="
        display: flex;
        flex-direction: column;
        gap: 10px;
        height: 500px;
        width: 200px;        /* largeur fixe */
        overflow-y: auto;
        border: 1px solid #ccc;
        padding: 5px;
    ">
        <?php
        $stmt = $pdo->prepare("SELECT image_path FROM images_upload WHERE user_id = :uid ORDER BY id DESC");
        $stmt->execute([':uid' => $_SESSION['user_id']]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($images as $img) {
            $path = htmlspecialchars($img['image_path']);
            echo "<div style='border:1px solid #aaa; padding:2px;'>
            <img src='$path' style='max-width:150px; display:block;'>
            </div>";
        }
        ?>
    </div>
    
    <!-- Canvas -->
    <canvas id="preview" width="800" height="600" style="border:1px solid #ccc;"></canvas>
</div>

<div id="stickersGallery" style="
    display: flex;
    gap: 10px;
    overflow-x: auto;
    overflow-y: hidden;
    white-space: nowrap;
    margin-left: 325px;
    width: 600px;       /* adapte selon ton layout */
    max-width: 100%;
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 8px;
">
    <?php
    $stickers = glob($stickersDir . "*.png");
    foreach ($stickers as $s) {
        $name = basename($s);
        echo "<img src='stickers/$name' 
                   data-sticker='$name' 
                   style='width: 80px; height: 80px; object-fit: contain; cursor: pointer; border: 2px solid transparent; border-radius: 8px;' 
                   class='sticker-item'>";
    }
    ?>
</div>
<div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
    <input type="file" id="fileInput" accept="image/*">
    <button id="clearFileBtn">❌ Supprimer</button>


        <button id="snapBtn" disabled>Prendre / Uploader la photo</button>
    <button id="camToggleBtn">Camera on/off</button>
</div>

<form method="POST" action="upload.php" enctype="multipart/form-data" id="uploadForm" style="display:none;">
    <input type="hidden" name="photo" id="photoInput">
    <input type="hidden" name="sticker" id="stickerHidden">
</form>

<script>
let stream = null;
const video = document.createElement('video');
video.autoplay = true;
video.playsInline = true;
video.setAttribute("muted", true); // important pour Chrome/iOS
video.style.display = "none"; // on l'ajoute au DOM mais caché
document.body.appendChild(video);

const preview = document.getElementById('preview');
const ctx = preview.getContext('2d');
const snapBtn = document.getElementById('snapBtn');
const stickerSelect = document.getElementById('stickerWebcam');
const photoInput = document.getElementById('photoInput');
const stickerHidden = document.getElementById('stickerHidden');
const fileInput = document.getElementById('fileInput');
const clearFileBtn = document.getElementById('clearFileBtn');
const camToggleBtn = document.getElementById('camToggleBtn');

let selectedSticker = null;
let stickerImg = new Image();

document.querySelectorAll('.sticker-item').forEach(img => {
    img.addEventListener('click', () => {
        // Désélectionner les autres
        document.querySelectorAll('.sticker-item').forEach(i => i.style.border = "2px solid transparent");
        
        // Marquer celui-ci comme sélectionné
        img.style.border = "2px solid #00aaff";
        selectedSticker = img.dataset.sticker;
        
        // Charger le sticker dans le canvas
        stickerImg.src = 'stickers/' + selectedSticker;
        stickerHidden.value = selectedSticker;

        snapBtn.disabled = (!selectedSticker || (!fileInput.files.length && !stream));
    });
});


// Gestion caméra
function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: true })
    .then(s => {
        console.log("✅ Webcam activée");
        stream = s;
        video.srcObject = stream;
        snapBtn.disabled = !selectedSticker || (!uploadedImg && !stream);
        video.play().then(() => {
            console.log("🎥 Lecture lancée");
        }).catch(err => console.error("❌ play() a échoué :", err));
    })
    .catch(err => {
        console.error("❌ Webcam inaccessible :", err);
        alert("Erreur caméra : " + err.message);
    });
}


function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
        camToggleBtn.textContent = "Camera on/off";
        snapBtn.disabled = fileInput.files.length === 0;
    }
}

camToggleBtn.addEventListener('click', (e) => {
    e.preventDefault();
    if(stream) stopCamera(); else startCamera();
});

// Lecture du fichier uploadé
let uploadedImg = null;
fileInput.addEventListener('change', () => {
    if (!fileInput.files.length) {
        uploadedImg = null;
        snapBtn.disabled = !stream;
        return;
    }
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
    snapBtn.disabled = (!uploadedImg && !stream) || !selectedSticker;
});

// Dessin en boucle
function drawLoop() {
    const canvasW = preview.width;
    const canvasH = preview.height;

    ctx.clearRect(0, 0, canvasW, canvasH);

    if (uploadedImg) {
        const ratio = Math.min(canvasW / uploadedImg.width, canvasH / uploadedImg.height);
        const drawW = uploadedImg.width * ratio;
        const drawH = uploadedImg.height * ratio;
        const offsetX = (canvasW - drawW) / 2;
        const offsetY = (canvasH - drawH) / 2;
        ctx.drawImage(uploadedImg, offsetX, offsetY, drawW, drawH);
    } else if (stream) {
        ctx.drawImage(video, 0, 0, canvasW, canvasH);
    }

    if (stickerImg.complete && stickerImg.naturalWidth > 0) {
        const sw = preview.width;   // largeur du canvas
        const sh = preview.height;  // hauteur du canvas
        ctx.drawImage(stickerImg, 0, 0, sw, sh);
    }

    requestAnimationFrame(drawLoop);
}

drawLoop();

// Capture / upload
// Capture / upload
snapBtn.addEventListener('click', async (e)=>{
    e.preventDefault();

    if (!selectedSticker) {
        alert("❌ Sélectionne d'abord un sticker !");
        return;
    }

    // Convertir le canvas en base64
    const photoData = preview.toDataURL('image/png');

    // Préparer les données pour le backend
    const formData = new FormData();
    formData.append('photo', photoData);
    formData.append('sticker', selectedSticker);

    try {
        const res = await fetch('upload.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.text();

        // 🔍 Optionnel : log si tu veux débugger
        console.log("Réponse serveur :", data);

        // ✅ On ajoute directement la nouvelle image à la galerie
        // Le backend enregistre les fichiers dans /uploads/
        const match = data.match(/uploads\/img_[^'"\s]+\.png/);
        if (match) {
            const newImg = document.createElement('img');
            newImg.src = match[0];
            newImg.style.maxWidth = "150px";
            newImg.style.display = "block";

            const div = document.createElement('div');
            div.style.border = "1px solid #aaa";
            div.style.padding = "2px";
            div.appendChild(newImg);

            document.getElementById('gallery').prepend(div);
        }

    } catch (err) {
        console.error("❌ Erreur fetch :", err);
        alert("Erreur lors de l'envoi de la photo");
    }
});



// Démarrage initial caméra
startCamera();
</script>

<?php include 'footer.php'; ?>
</body>
</html>