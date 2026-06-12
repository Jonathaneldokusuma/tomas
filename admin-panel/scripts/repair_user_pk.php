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

// Step 1: update id_user=0 to 1 (only if id_user=1 doesn't exist)
$res = $mysqli->query('SELECT COUNT(*) AS cnt FROM user WHERE id_user = 1');
$row = $res->fetch_assoc();
if ($row['cnt'] > 0) {
    echo "id_user=1 already exists, aborting update.\n";
} else {
    $mysqli->query('UPDATE `user` SET id_user = 1 WHERE id_user = 0');
    echo "Updated rows to set id_user=1, affected: " . $mysqli->affected_rows . "\n";
}

// Step 2: modify column to unsigned AUTO_INCREMENT
if (! $mysqli->query('ALTER TABLE `user` MODIFY `id_user` INT UNSIGNED NOT NULL AUTO_INCREMENT')) {
    fwrite(STDERR, "ALTER failed: ({$mysqli->errno}) {$mysqli->error}\n");
    $mysqli->close();
    exit(2);
}

// Step 3: set AUTO_INCREMENT to max(id_user)+1
$res2 = $mysqli->query('SELECT MAX(id_user) AS maxid FROM user');
$row2 = $res2->fetch_assoc();
$next = ((int)$row2['maxid']) + 1;
if ($next <= 1) $next = 2;
$mysqli->query('ALTER TABLE `user` AUTO_INCREMENT = ' . $next);

echo "Converted id_user to INT UNSIGNED AUTO_INCREMENT; next AUTO_INCREMENT={$next}\n";

$mysqli->close();
