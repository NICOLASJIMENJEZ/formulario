<?php
header('Content-Type: application/json');
require_once "db.php"; // si usas db.php para conexión, si no cámbialo

try {
    // Cuenta cuántos registros tienen arrived_count > 0
    $sql = "SELECT COUNT(*) AS total FROM registros WHERE arrived_count > 0";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "total" => intval($row["total"])
    ]);
} 
catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "total" => 0,
        "error" => $e->getMessage()
    ]);
}

