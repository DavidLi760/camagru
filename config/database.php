<?php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'camagru';
$DB_USER = getenv('USER');  // ton login 42
$DB_PASS = 'nano ~/camagru/config/database.p;'              // vide si pas de mot de passe

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur connexion DB: " . $e->getMessage());
}
?>

