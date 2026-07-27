<?php
/**
 * Script d'auto-configuration de la base de donnees.
 * Cree la base de donnees MySQL si elle n'existe pas.
 * Utilise par start_saas_app.bat au premier lancement.
 */

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'saas_accounting';

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "[+] Base de donnees '$dbname' prete." . PHP_EOL;
    exit(0);
} catch (Exception $e) {
    echo "[ERREUR] Impossible de se connecter a MySQL." . PHP_EOL;
    echo "    Verifiez que Laragon est demarre avec MySQL actif." . PHP_EOL;
    echo "    Detail : " . $e->getMessage() . PHP_EOL;
    exit(1);
}
