<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'dpg-d3sgqbk9c44c73cqc9p0-a.oregon-postgres.render.com';
$port = 5432;
$dbname = 'base_de_datos_graduandos_yise';
$username = 'base_de_datos_graduandos_yise_user';
$password = 'bFNFGEUwRNts0j15phJfE4Nklpo4KetS';

try {

    // Render necesita sslrootcert=system para que PDO no rompa la conexión
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;sslrootcert=system";

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (PDOException $e) {
    die("❌ Error al conectar con PostgreSQL: " . $e->getMessage());
}
?>
