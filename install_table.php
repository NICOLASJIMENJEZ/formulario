<?php
// install_table.php - crea la tabla `registros` en la DB configurada en db.php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if (isset($USE_FILE_STORAGE) && $USE_FILE_STORAGE === true) {
    echo json_encode(['success'=>false,'message'=>'El proyecto está configurado para almacenamiento en archivo. Cambia $USE_FILE_STORAGE a false en db.php para crear la tabla.']);
    exit;
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    echo json_encode(['success'=>false,'message'=>'No hay conexión PDO disponible. Revisa credenciales en db.php.']);
    exit;
}

try {
    $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS registros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titular_nombre VARCHAR(100) NOT NULL,
  titular_apellidos VARCHAR(100) NOT NULL,
  titular_cc VARCHAR(20),
  titular_celular VARCHAR(20),
  titular_correo VARCHAR(100),
  invitado1_nombre VARCHAR(100),
  invitado1_apellidos VARCHAR(100),
  invitado1_cc VARCHAR(20),
  invitado2_nombre VARCHAR(100),
  invitado2_apellidos VARCHAR(100),
  invitado2_cc VARCHAR(20),
  invitado3_nombre VARCHAR(100),
  invitado3_apellidos VARCHAR(100),
  invitado3_cc VARCHAR(20),
  discapacidad VARCHAR(2) DEFAULT 'no',
  discapacidad_cual VARCHAR(255),
  fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

    $pdo->exec($sql);
    echo json_encode(['success'=>true,'message'=>'Tabla registros creada o ya existente en la base de datos.']);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Error al crear la tabla: '.$e->getMessage()]);
}
