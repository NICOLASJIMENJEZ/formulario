<?php
header('Content-Type: application/json');
require_once "db.php";

try {

    // SUMA total de invitados que han llegado
    $sql = "SELECT SUM(arrived_count) AS total FROM registros";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "total" => intval($row["total"])
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "total" => 0,
        "error" => $e->getMessage()
    ]);
}


