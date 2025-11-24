<?php
$host = "dpg-d4gbeguuk2gs73chgoug-a.render.com";
$dbname = "base_de_datos_graduandos_qdwy";
$user = "base_de_datos_graduandos_qdwy_user";
$pass = "jNjdBda1YMu3XBFVRpmnTFPeZGZHjrj8";
$port = "5432";

// Render: usar sslmode=require (NO verify-full)
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Conexión exitosa";
} catch (Throwable $e) {
    die("❌ Error al conectar con PostgreSQL: " . $e->getMessage());
}
