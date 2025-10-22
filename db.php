<?php
// DB connection. If it fails, we fallback to file storage
$USE_FILE_STORAGE = false; // default: try DB first

// 🔧 Datos de conexión a Render PostgreSQL
$host = 'dpg-d3sgqbk9c44c73cqc9p0-a.oregon-postgres.render.com'; 
$port = 5432;
$dbname = 'base_de_datos_graduandos_yise';
$username = 'base_de_datos_graduandos_yise_user';
$password = 'bFNFGEUwRNts0j15phJfE4Nklpo4KetS';

try {
    // ✅ Conexión correcta para PostgreSQL
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // ⚠️ Si falla, cambia a modo archivo y guarda el error
    $USE_FILE_STORAGE = true;
    $pdo = null;

    $logDir = __DIR__ . '/data';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $msg = date('[Y-m-d H:i:s] ') . 'DB connection failed: ' . $e->getMessage() . PHP_EOL;
    @file_put_contents($logDir . '/debug.log', $msg, FILE_APPEND);
}
?>
