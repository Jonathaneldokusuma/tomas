<?php
// Reads DB credentials from environment variables, falling back to known defaults.
$host = getenv('DB_HOST') ?: 'zephyr.proxy.rlwy.net';
$port = getenv('DB_PORT') ?: '10768';
$db = getenv('DB_DATABASE') ?: 'railway';
$user = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '';
$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $row = $pdo->query('SELECT COUNT(*) AS cnt FROM tukang')->fetch(PDO::FETCH_ASSOC);
    echo "tukang_count:" . ($row['cnt'] ?? 0) . PHP_EOL;
    echo "sample_rows:\n";
    $stmt = $pdo->query('SELECT id_tukang, nama, status_aktif FROM tukang ORDER BY id_tukang LIMIT 10');
    foreach ($stmt as $r) {
        echo implode("\t", [$r['id_tukang'], $r['nama'], $r['status_aktif']]) . PHP_EOL;
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(2);
}
