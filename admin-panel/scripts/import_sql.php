<?php
// Simple MySQL importer using mysqli and DELIMITER handling.
// Usage: php import_sql.php "D:\\laravel web app\\tomas.sql"
ini_set('memory_limit','-1');
set_time_limit(0);

$host = getenv('DB_HOST') ?: 'zephyr.proxy.rlwy.net';
$port = getenv('DB_PORT') ?: '10768';
$dbname = getenv('DB_DATABASE') ?: 'railway';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'zBFtiDhzYImEnurWKqkuRqYbALTLtPbm';

$path = isset($argv[1]) ? $argv[1] : realpath(__DIR__ . '/../../tomas.sql');
if (!$path || !file_exists($path)) {
    fwrite(STDERR, "SQL file not found: {$path}\n");
    exit(2);
}

$mysqli = new mysqli($host, $user, $pass, $dbname, (int)$port);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "Connect failed: ({$mysqli->connect_errno}) {$mysqli->connect_error}\n");
    exit(3);
}

$fp = fopen($path, 'r');
if (!$fp) {
    fwrite(STDERR, "Unable to open file: {$path}\n");
    exit(4);
}

$delimiter = ';';
$buffer = '';
$lineNumber = 0;

while (!feof($fp)) {
    $line = fgets($fp);
    $lineNumber++;
    if ($line === false) break;
    $trimLine = trim($line);

    // handle DELIMITER statements
    if (preg_match('/^DELIMITER\s+(.+)$/i', $trimLine, $m)) {
        $delimiter = $m[1];
        continue;
    }

    // skip SQL comments (simple heuristic)
    if (preg_match('/^\s*(-- |#|\/\*)/', $line)) {
        $buffer .= $line;
        continue;
    }

    $buffer .= $line;
    $trimmed = trim($buffer);

    $delLen = strlen($delimiter);
    if ($delLen === 0) {
        continue;
    }

    if (strlen($trimmed) >= $delLen && substr($trimmed, -$delLen) === $delimiter) {
        $sql = substr($buffer, 0, -$delLen);
        if (trim($sql) !== '') {
            if (!$mysqli->multi_query($sql)) {
                fwrite(STDERR, "Error at approx line {$lineNumber}: ({$mysqli->errno}) {$mysqli->error}\n");
                fclose($fp);
                $mysqli->close();
                exit(5);
            }
            // consume all results
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
        }
        $buffer = '';
    }
}

fclose($fp);
$mysqli->close();

echo "Import completed successfully.\n";
exit(0);
