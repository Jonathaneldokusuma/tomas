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
$res = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'railway' AND TABLE_NAME = 'user' AND COLUMN_NAME = 'password'");
$row = $res->fetch_assoc();
if ($row['cnt'] > 0) {
    echo "password column already exists\n";
    $mysqli->close();
    exit(0);
}
if (! $mysqli->query("ALTER TABLE `user` ADD COLUMN `password` varchar(255) NOT NULL DEFAULT '' AFTER `no_hp`")) {
    fwrite(STDERR, "ALTER failed: ({$mysqli->errno}) {$mysqli->error}\n");
    $mysqli->close();
    exit(2);
}
echo "Added password column to user table\n";
$mysqli->close();
