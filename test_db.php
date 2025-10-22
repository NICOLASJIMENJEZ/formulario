<?php
require_once __DIR__ . '/db.php';

if (isset($pdo) && $pdo instanceof PDO) {
    echo "<h3 style='color:green'>✅ Conexión exitosa a la base de datos</h3>";
    $stmt = $pdo->query("SELECT NOW()");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Servidor responde: " . $row['now'];
} else {
    echo "<h3 style='color:red'>❌ Error al conectar con la base de datos</h3>";
    echo "<pre>";
    @readfile(__DIR__ . '/data/debug.log');
    echo "</pre>";
}
?>
