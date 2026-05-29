<?php
$host = getenv('DB_HOST') ?: 'zephyr.proxy.rlwy.net';
$port = getenv('DB_PORT') ?: '10768';
$dbname = getenv('DB_DATABASE') ?: 'railway';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'zBFtiDhzYImEnurWKqkuRqYbALTLtPbm';

$mysqli = new mysqli($host, $user, $pass, $dbname, (int)$port);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "Connect failed: ({$mysqli->connect_errno}) {$mysqli->connect_error}\n");
    exit(1);
}
$res = $mysqli->query('SELECT COUNT(*) AS cnt FROM user WHERE id_user = 1');
if (!$res) { echo "Query failed\n"; exit(2); }
$row = $res->fetch_assoc();
echo "id_1_count=" . $row['cnt'] . "\n";
$mysqli->close();
