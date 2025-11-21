<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datos de conexión Render PostgreSQL
$host = 'dpg-d3sgqbk9c44c73cqc9p0-a.oregon-postgres.render.com';
$port = 5432;
$dbname = 'base_de_datos_graduandos_yise';
$username = 'base_de_datos_graduandos_yise_user';
$password = 'bFNFGEUwRNts0j15phJfE4Nklpo4KetS';

try {

    // Render exige SSL obligatorio
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

} catch (PDOException $e) {
    die(json_encode([
        "success" => false,
        "message" => "❌ Error al conectar con PostgreSQL: " . $e->getMessage()
    ]));
}
?>
