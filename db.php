<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$USE_FILE_STORAGE = false;

$host = 'dpg-d3sgqbk9c44c73cqc9p0-a.oregon-postgres.render.com';
$port = 5432;
$dbname = 'base_de_datos_graduandos_yise';
$username = 'base_de_datos_graduandos_yise_user';
$password = 'bFNFGEUwRNts0j15phJfE4Nklpo4KetS';

try {

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::PGSQL_ATTR_SSL_MODE => 'require' // 🔥 obligatorio en Render
    ];

    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $username,
        $password,
        $options
    );

} catch (PDOException $e) {

    die("❌ ERROR AL CONECTAR A RENDER POSTGRES: " . $e->getMessage());
}
?>
