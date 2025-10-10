<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($pdo) || !$pdo instanceof PDO) {
    echo json_encode(['success'=>false,'message'=>'No hay conexión PDO. Verifica db.php']);
    exit;
}
try {
    // Verificar si la columna existe
    $cols = ['hora' => "TIME NULL", 'programa' => "VARCHAR(255) NULL"];
    foreach ($cols as $col => $type) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM registros LIKE ?");
        $stmt->execute([$col]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            $pdo->exec("ALTER TABLE registros ADD COLUMN $col $type");
        }
    }
    echo json_encode(['success'=>true,'message'=>'Columnas verificadas/añadidas.']);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
