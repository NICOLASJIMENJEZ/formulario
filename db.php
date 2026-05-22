<?php
// Configuración de base de datos PostgreSQL en Render
$host = "a45p_user:cxIm2wuUm9pZNauvVX2LauO0HGoLEFOm@dpg-d883jnmq1p3s73fsmdf0-a.oregon-postgres.render.com"; // HOSTNAME COMPLETO
$dbname = "base_de_datos_graduandos_a45p";
$user = "base_de_datos_graduandos_a45p_user";
$pass = "cxIm2wuUm9pZNauvVX2LauO0HGoLEFOm";
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
