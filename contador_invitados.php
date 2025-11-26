<?php
header("Content-Type: application/json; charset=utf-8");

// Conexión Render PostgreSQL
$host     = "dpg-d4gbeguuk2gs73chgoug-a.oregon-postgres.render.com";
$port     = "5432";
$dbname   = "base_de_datos_graduandos_qdwy";
$user     = "base_de_datos_graduandos_qdwy_user";
$password = "jNjdBda1YMu3XBFVRpmnTFPeZGZHjrj8";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Contar invitados
    $sql = "SELECT COUNT(*) AS total_invitados FROM registros WHERE tipo = 'Invitado'";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "ok" => true,
        "total_invitados" => $row["total_invitados"]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "ok" => false,
        "error" => $e->getMessage()
    ]);
}

