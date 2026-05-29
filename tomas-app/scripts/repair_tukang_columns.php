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
$cols = [
    "kategori VARCHAR(100) DEFAULT NULL",
    "lokasi VARCHAR(200) DEFAULT NULL",
    "bio TEXT DEFAULT NULL"
];
foreach ($cols as $colDef) {
    preg_match('/^([a-zA-Z0-9_]+)/', $colDef, $m);
    $colName = $m[1];
    $res = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'railway' AND TABLE_NAME = 'tukang' AND COLUMN_NAME = '" . $mysqli->real_escape_string($colName) . "'");
    $row = $res->fetch_assoc();
    if ($row['cnt'] == 0) {
        $sql = "ALTER TABLE `tukang` ADD COLUMN `" . $colName . "` " . substr($colDef, strlen($colName) + 1);
        if (! $mysqli->query($sql)) {
            fwrite(STDERR, "Failed to add $colName: ({$mysqli->errno}) {$mysqli->error}\n");
            $mysqli->close();
            exit(2);
        }
        echo "Added column $colName\n";
    } else {
        echo "Column $colName already exists\n";
    }
}
$mysqli->close();
