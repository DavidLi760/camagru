<?php
$pdo = new PDO("sqlite:" . __DIR__ . "/../config/camagru.sqlite");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>

