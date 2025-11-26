<?php
header('Content-Type: application/json');

// Conexión a PostgreSQL (usa tus mismos datos)
$host = "dpg-d4gbeguuk2gs73chgoug-a.oregon-postgres.render.com";
$db   = "base_de_datos_graduandos_qdwy";
$user = "base_de_datos_graduandos_qdwy_user";
$pass = "jNjdBda1YMu3XBFVRpmnTFPeZGZHjrj8";
$port = "5432";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Contar cuántos invitados han sido marcados con 1, 2 o 3 (semaforo)
    $sql = "SELECT COUNT(*) AS total FROM registros WHERE invitado_semaforo IS NOT NULL";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "invitados" => $result["total"]
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
