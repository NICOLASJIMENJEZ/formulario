<?php
$host = "dpg-d4gbeguuk2gs73chgoug-a.render.com";
$dbname = "base_de_datos_graduandos_qdwy"; 
$user = "base_de_datos_graduandos_qdwy_user";
$pass = "jNjdBda1YMu3XBFVRpmnTFPeZGZHjrj8";
$port = "5432";

// IMPORTANTE: Render exige verify-full y CA correcta
$dsn = "pgsql:host=$host;port=5432;dbname=$dbname;sslmode=verify-full";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Throwable $e) {
    die("❌ Error al conectar con PostgreSQL: " . $e->getMessage());
}

