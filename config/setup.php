<?php
require_once __DIR__ . '/database.php';

try {
    // Table des utilisateurs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            confirmation_token TEXT,   -- token unique envoyé par mail
	    reset_token TEXT,          -- token pour réinitialisation de mot de passe
            is_confirmed INTEGER DEFAULT 0, -- 0 = pas confirmé, 1 = confirmé
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");


    // Table des images
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            image_path TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    // Table des commentaires
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            image_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE
        );
    ");

    // Table des likes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS likes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            image_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
            UNIQUE(user_id, image_id)
        );
    ");

    echo "✅ Base SQLite et tables créées avec succès !\n";
} catch (PDOException $e) {
    die("❌ Erreur lors de la création des tables : " . $e->getMessage());
}
