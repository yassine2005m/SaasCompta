<?php
// DIAGNOSTIC FILE - DELETE AFTER USE
echo "<h2>Diagnostic du Serveur</h2>";

echo "<h3>1. Version PHP</h3>";
echo "PHP " . phpversion() . "<br>";

echo "<h3>2. Extensions PHP</h3>";
$required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo'];
foreach ($required as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌ MANQUANT';
    echo "$ext : $status<br>";
}

echo "<h3>3. Permissions des dossiers</h3>";
$base = dirname(__DIR__);
$dirs = ['storage', 'storage/logs', 'storage/framework', 'storage/framework/sessions', 'storage/framework/views', 'storage/framework/cache', 'bootstrap/cache'];
foreach ($dirs as $dir) {
    $path = $base . '/' . $dir;
    if (file_exists($path)) {
        echo "$dir : " . (is_writable($path) ? '✅ Accessible en écriture' : '❌ PAS accessible en écriture') . "<br>";
    } else {
        echo "$dir : ❌ N'EXISTE PAS<br>";
    }
}

echo "<h3>4. Fichier .env</h3>";
$envPath = $base . '/.env';
echo file_exists($envPath) ? '✅ .env existe' : '❌ .env MANQUANT';
echo "<br>";

echo "<h3>5. Test de connexion DB</h3>";
try {
    $host = 'sql112.infinityfree.com';
    $db = 'if0_41977791_saascomptabilite';
    $user = 'if0_41977791';
    $pass = 'Yassine198300';
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "✅ Connexion MySQL réussie !<br>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables trouvées : " . count($tables) . "<br>";
    foreach ($tables as $t) echo "- $t<br>";
} catch (Exception $e) {
    echo "❌ Erreur MySQL : " . $e->getMessage() . "<br>";
}

echo "<h3>6. Erreur Laravel</h3>";
$logFile = $base . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -30);
    echo "<pre>" . htmlspecialchars(implode("", $lines)) . "</pre>";
} else {
    echo "❌ Pas de fichier de log Laravel<br>";
}

echo "<h3>7. Structure htdocs</h3>";
$htdocs = $base;
$items = scandir($htdocs);
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    echo (is_dir($htdocs.'/'.$item) ? '📁' : '📄') . " $item<br>";
}
