<?php
require __DIR__ . '/includes/bootstrap.php';
require_role('admin');

// Set headers to force a file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="mamatrack_backup_' . date('Ymd_His') . '.sql"');
header('Pragma: no-cache');
header('Expires: 0');

$pdo = db();

echo "-- MamaTrack Database Backup\n";
echo "-- Generated: " . now() . "\n\n";
echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

// Get all tables in the database
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    // 1. Get the CREATE TABLE statement
    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
    echo "DROP TABLE IF EXISTS `$table`;\n";
    echo $create[1] . ";\n\n";

    // 2. Get all data from the table
    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rows as $row) {
        $vals = array_map(function($v) use ($pdo) {
            return $v === null ? 'NULL' : $pdo->quote($v);
        }, $row);
        echo "INSERT INTO `$table` VALUES (" . implode(',', $vals) . ");\n";
    }
    echo "\n";
}

echo "SET FOREIGN_KEY_CHECKS=1;\n";
audit('backup_downloaded_pure_php');
exit;
