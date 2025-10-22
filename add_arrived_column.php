<?php
// Simple migration: adds arrived_count column to registros table.
// WARNING: Run this only if you use MySQL and have proper backups.

require_once __DIR__ . '/../db.php';
header('Content-Type: text/plain; charset=utf-8');

if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo "PDO not available. Ensure db.php is configured for MySQL.\n";
    exit(1);
}

try {
    $pdo->exec("ALTER TABLE registros ADD COLUMN arrived_count TINYINT(1) DEFAULT 0 AFTER fecha_hora");
    echo "Column 'arrived_count' added successfully.\n";
} catch (Throwable $e) {
    echo "Failed to add column: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/../data/debug.log', date('[Y-m-d H:i:s] ') . "Migration failed: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    exit(1);
}

return 0;
