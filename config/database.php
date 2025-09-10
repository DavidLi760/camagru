<?php
// Chemin vers le fichier SQLite (sera créé automatiquement si absent)
$DB_FILE = __DIR__ . '/camagru.sqlite';

try {
    $pdo = new PDO("sqlite:" . $DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Activer les clés étrangères
    $pdo->exec("PRAGMA foreign_keys = ON;");
} catch (PDOException $e) {
    die("❌ Erreur de connexion SQLite : " . $e->getMessage());
}
