<?php
// DB connection. If it fails, we fallback to file storage by setting $USE_FILE_STORAGE = true
$USE_FILE_STORAGE = false; // default: try DB first

$host = '127.0.0.1';  // o 'localhost'
$port = 3307;          // Puerto personalizado
$dbname = 'graduados';  // Cambia por tu DB real
$username = 'root';     // Usuario MySQL
$password = '903135Nicolas';  // Contrasea MySQL

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Do not output HTML or die - enable file storage fallback and log the error for debugging.
    $USE_FILE_STORAGE = true;
    $pdo = null;
    // Ensure data directory exists
    $logDir = __DIR__ . '/data';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $msg = date('[Y-m-d H:i:s] ') . 'DB connection failed: ' . $e->getMessage() . PHP_EOL;
    @file_put_contents($logDir . '/debug.log', $msg, FILE_APPEND);
}
