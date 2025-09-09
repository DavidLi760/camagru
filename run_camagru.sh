#!/bin/bash

# -----------------------------
# Config MySQL / Camagru
# -----------------------------
DB_NAME="camagru"
DB_HOST="mysql.42.fr"
DB_USER="$USER"       # ton login 42
DB_PASS="Nebulios760@"            # laisse vide si pas de mot de passe
DB_HOST="127.0.0.1"

# Chemin du projet
PROJECT_DIR="$(pwd)"

# Port PHP intégré
PHP_PORT=8081

# -----------------------------
# Vérifier / créer dossier images
# -----------------------------
if [ ! -d "$PROJECT_DIR/images" ]; then
    echo "Création du dossier images/..."
    mkdir "$PROJECT_DIR/images"
    chmod 755 "$PROJECT_DIR/images"
fi

# -----------------------------
# Lancer le serveur PHP intégré
# -----------------------------
echo "Lancement de Camagru sur http://127.0.0.1:$PHP_PORT ..."
php -S 127.0.0.1:$PHP_PORT -t "$PROJECT_DIR/public"

