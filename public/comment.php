<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'], $_POST['image_id'], $_POST['content'])) {
    exit;
}

$userId = $_SESSION['user_id'];
$imageId = $_POST['image_id'];
$content = trim($_POST['content']);

if ($content === '') exit;

// 1️⃣ Ajouter le commentaire dans la base
$stmt = $pdo->prepare("
    INSERT INTO comments (user_id, image_id, content)
    VALUES (:uid, :iid, :content)
");
$stmt->execute([
    ':uid' => $userId,
    ':iid' => $imageId,
    ':content' => $content
]);

// 2️⃣ Récupérer l'auteur et le chemin de l'image
$stmt = $pdo->prepare("
    SELECT u.email, u.username, u.notify_on_comment, i.image_path
    FROM images i
    JOIN users u ON u.id = i.user_id
    WHERE i.id = :image_id
");
$stmt->execute([':image_id' => $imageId]);
$imageAuthor = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'image n'existe pas, on stoppe
if (!$imageAuthor) {
    die("Erreur : image introuvable !");
}

// 3️⃣ Définir le nom de fichier pour la redirection
$filename = basename($imageAuthor['image_path']);

// 4️⃣ Envoyer le mail si l'auteur veut être notifié
if (!empty($imageAuthor['notify_on_comment'])) {
    $to = $imageAuthor['email'];
    $subject = "Nouveau commentaire sur votre photo Camagru";
    $body = "Bonjour " . $imageAuthor['username'] . ",\n\n"
          . $_SESSION['username'] . " a commenté votre photo :\n\n"
          . "\"$content\"\n\n"
          . "Voir la photo : http://localhost:8080/photo.php?file=" . urlencode($filename);

    $headers = "From: noreply@camagru.local\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    mail($to, $subject, $body, $headers);
}

// 5️⃣ Redirection vers la page de la photo
header("Location: photo.php?file=" . urlencode($filename));
exit;
?>
