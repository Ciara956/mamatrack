<?php
require __DIR__ . '/bootstrap.php';
require_role('admin');

// MySQL Backup using mysqldump
$dbName = 'mamatrack';
$dbUser = 'root';
$dbPass = ''; // Change if needed
$backupFile = sys_get_temp_dir() . '/mamatrack_backup_' . date('Ymd_His') . '.sql';

// Execute mysqldump command
$command = "mysqldump --user=$dbUser --password=$dbPass $dbName > $backupFile";
exec($command, $output, $returnVar);

if ($returnVar === 0 && file_exists($backupFile)) {
    audit('backup_downloaded');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="mamatrack_backup_' . date('Ymd_His') . '.sql"');
    header('Content-Length: ' . filesize($backupFile));
    readfile($backupFile);
    unlink($backupFile);
} else {
    flash('Backup failed. Ensure mysqldump is in your system PATH.', 'err');
    redirect('users.php');
}
