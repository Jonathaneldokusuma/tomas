<?php
ini_set('memory_limit','-1');
set_time_limit(0);
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

$tables = ['tukang', 'favorit'];
foreach ($tables as $t) {
    $res = $mysqli->query("SHOW CREATE TABLE `" . $mysqli->real_escape_string($t) . "`");
    if (!$res) {
        echo "No table: $t\n";
        continue;
    }
    $row = $res->fetch_assoc();
    echo "===== $t =====\n";
    echo $row['Create Table'] . "\n\n";
}

$mysqli->close();
