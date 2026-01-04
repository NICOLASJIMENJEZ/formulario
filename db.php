<?php
$host = dpg-d54ojin5r7bs73ejo270-a;
$dbname = graduados_grf8
$user = graduados_grf8_user
$pass = nlCe8T5g1nGqjgl2iuKSysTesfYKs0wb
$port = "5432";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Throwable $e) {
    die(json_encode([
        'success' => false,
        'message' => "Error al conectar con PostgreSQL: " . $e->getMessage()
    ]));
}
