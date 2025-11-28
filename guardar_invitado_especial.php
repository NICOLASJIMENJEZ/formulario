<?php
require_once __DIR__ . '/db.php';

// -------------------------
// RECIBIR DATOS DEL FORM
// -------------------------

$id_especial = $_POST['id_especial'];

$titular_nombre        = $_POST['nombre'];
$titular_apellidos     = $_POST['apellidos'];
$titular_cc            = $_POST['cc'];
$discapacidad          = $_POST['discapacidad'] ?? 'no';
$discapacidad_cual     = $_POST['discapacidad_cual'] ?? null;

$hora                  = $_POST['hora'];
$programa              = $_POST['programa'];
$es_especial           = 1;

// INVITADOS

$in1_nombre        = $_POST['invitado1_nombre'] ?? null;
$in1_apellidos     = $_POST['invitado1_apellidos'] ?? null;
$in1_cc            = $_POST['invitado1_cc'] ?? null;
$in1_disca         = $_POST['invitado1_discapacidad'] ?? null;

$in2_nombre        = $_POST['invitado2_nombre'] ?? null;
$in2_apellidos     = $_POST['invitado2_apellidos'] ?? null;
$in2_cc            = $_POST['invitado2_cc'] ?? null;
$in2_disca         = $_POST['invitado2_discapacidad'] ?? null;

$in3_nombre        = $_POST['invitado3_nombre'] ?? null;
$in3_apellidos     = $_POST['invitado3_apellidos'] ?? null;
$in3_cc            = $_POST['invitado3_cc'] ?? null;
$in3_disca         = $_POST['invitado3_discapacidad'] ?? null;


// -------------------------
// INSERTAR EN LA BASE
// -------------------------

$stmt = $pdo->prepare("
    INSERT INTO registros (
        titular_nombre, titular_apellidos, titular_cc, discapacidad, discapacidad_cual,
        invitado1_nombre, invitado1_apellidos, invitado1_cc, invitado1_discapacidad,
        invitado2_nombre, invitado2_apellidos, invitado2_cc, invitado2_discapacidad,
        invitado3_nombre, invitado3_apellidos, invitado3_cc, invitado3_discapacidad,
        hora, programa, es_especial, id_especial
    ) VALUES (?, ?, ?, ?, ?,
              ?, ?, ?, ?,
              ?, ?, ?, ?,
              ?, ?, ?, ?,
              ?, ?, ?, ?)
");

$ok = $stmt->execute([
    $titular_nombre, $titular_apellidos, $titular_cc, $discapacidad, $discapacidad_cual,
    $in1_nombre, $in1_apellidos, $in1_cc, $in1_disca,
    $in2_nombre, $in2_apellidos, $in2_cc, $in2_disca,
    $in3_nombre, $in3_apellidos, $in3_cc, $in3_disca,
    $hora, $programa, $es_especial, $id_especial
]);

// -------------------------
// RESPUESTA
// -------------------------

if ($ok) {
    header("Location: registros.php?msg=Invitado Especial registrado&id=$id_especial&type=success");
    exit;
} else {
    echo "Error al guardar los datos.";
}
