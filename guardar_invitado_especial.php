<?php
require_once __DIR__ . '/db.php';

$id_especial = $_POST['id_especial'];
$nombre = $_POST['nombre'];
$apellidos = $_POST['apellidos'];
$cc = $_POST['cc'];
$hora = $_POST['hora'];
$programa = $_POST['programa'];
$es_especial = 1;

// Guardar en la misma tabla REGISTROS
$stmt = $pdo->prepare("
INSERT INTO registros (
    titular_nombre, titular_apellidos, titular_cc,
    hora, programa,
    es_especial,
    id_especial
) VALUES (?, ?, ?, ?, ?, ?, ?)
");

$ok = $stmt->execute([
    $nombre,
    $apellidos,
    $cc,
    $hora,
    $programa,
    $es_especial,
    $id_especial
]);

if ($ok) {
    header("Location: registros.php?msg=Invitado Especial registrado&id=$id_especial&type=success");
    exit;
} else {
    echo "Error al guardar.";
}
