<?php
header('Content-Type: application/json');
require "conexion.php"; // misma conexión que invitados

$action = $_GET["action"] ?? $_POST["action"] ?? "";

function out($ok, $msg="", $extra=[]){
  echo json_encode(array_merge(["success"=>$ok, "message"=>$msg], $extra));
  exit;
}

/* ✔ LISTAR SOLO GRADUANDOS */
if($action == "list"){
    $sql = "SELECT id, nombre, apellido, cedula, programa, correo, hora, semaforo 
            FROM registros 
            WHERE tipo = 'graduando'
            ORDER BY id DESC";
    $q = $pdo->query($sql);
    out(true, "", ["records"=>$q->fetchAll(PDO::FETCH_ASSOC)]);
}

/* ✔ ACTUALIZAR */
if($action == "update"){
    $id = $_POST["id"];
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $cedula = $_POST["cedula"];
    $programa = $_POST["programa"];
    $correo = $_POST["correo"];
    $hora = $_POST["hora"];

    $sql = "UPDATE registros SET 
            nombre=?, apellido=?, cedula=?, programa=?, correo=?, hora=?
            WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([$nombre,$apellido,$cedula,$programa,$correo,$hora,$id]);

    out($ok, $ok ? "Actualizado" : "Error");
}

/* ✔ SEMÁFORO (visto = verde) */
if($action == "semaforo"){
    $id = $_POST["id"];
    $estado = $_POST["estado"]; // 1 = verde

    $stmt = $pdo->prepare("UPDATE registros SET semaforo=? WHERE id=?");
    $ok = $stmt->execute([$estado, $id]);

    out($ok, $ok ? "OK" : "Error");
}

/* ✔ ELIMINAR */
if($action == "delete"){
    $id = $_GET["id"];
    $stmt = $pdo->prepare("DELETE FROM registros WHERE id=?");
    $ok = $stmt->execute([$id]);
    out($ok);
}

out(false, "Acción no válida");
