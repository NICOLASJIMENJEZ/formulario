<?php
$host = "dpg-d5pn3o75r7bs73d5if2g-a";
$dbname = "base_de_datos_graduandos_0852";
$user = "base_de_datos_graduandos_0852_user";
$pass = "Dp3mIyZaPKRlOPKbD42enOaGbccgMBt4";
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
