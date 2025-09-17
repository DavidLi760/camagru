<?php
$to = "tonadresse@gmail.com";
$subject = "Test Camagru";
$body = "Bonjour, ceci est un test d'envoi depuis Camagru avec PHP.";
$headers = "From: tonadresse@gmail.com\r\n";

if (mail($to, $subject, $body, $headers)) {
    echo "✅ Email envoyé avec succès";
} else {
    echo "❌ Erreur lors de l'envoi du mail";
}

