<?php
// Configuración de base de datos PostgreSQL en Render
$host = "dpg-d5pn3o75r7bs73d5if2g-a.oregon-postgres.render.com"; // HOSTNAME COMPLETO
$dbname = "base_de_datos_graduandos_0852";
$user = "base_de_datos_graduandos_0852_user";
$pass = "Dp3mIyZaPKRlOPKbD42enOaGbccgMBt4";
$port = "5431";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Conexión exitosa
} catch (PDOException $e) {
    // Log del error (no mostrar detalles al usuario en producción)
    error_log("Error de conexión DB: " . $e->getMessage());
    
    // Respuesta genérica al usuario
    if (php_sapi_name() === 'cli') {
        die("Error de conexión a la base de datos.\n");
    } else {
        die(json_encode([
            'success' => false,
            'message' => "Error al conectar con la base de datos. Por favor, intenta más tarde."
        ]));
    }
}
?>
