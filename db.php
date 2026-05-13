<?php
// Configuración de base de datos PostgreSQL en Render
$host = "dpg-d7k1offavr4c73esdbeg-a.oregon-postgres.render.com"; // HOSTNAME COMPLETO
$dbname = "life_gym_db_hvmq";
$user = "life_gym_db_hvmq_user";
$pass = "lEovCr88q2giz5REW4MwUPePidNosjc1";
$port = "5432";

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
