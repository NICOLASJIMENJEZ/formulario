<?php
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=utf-8");

$action = $_REQUEST["action"] ?? "list";

/* ================================
   RESPUESTA
================================ */
function out($ok, $msg="", $extra=[]){
    echo json_encode(array_merge([
        "success" => $ok,
        "message" => $msg
    ], $extra));
    exit;
}

/* ================================
   1. LISTAR PROGRAMAS
================================ */
if ($action === "programs") {
    $sql = "SELECT DISTINCT programa FROM registros ORDER BY programa ASC";
    $rs = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    out(true, "", ["programs" => $rs]);
}

/* ================================
   2. LISTAR REGISTROS DE TITULARES
================================ */
if ($action === "list") {

    $sql = "SELECT id, titular_nombre, titular_apellidos, titular_cc, titular_celular, titular_correo, hora, programa, discapacidad, arrived_count
            FROM registros WHERE 1 ";
    $params = [];

    // BUSCADOR
    if (isset($_GET["q"]) && $_GET["q"] !== "") {
        $sql .= " AND (titular_nombre ILIKE :q OR titular_apellidos ILIKE :q OR titular_cc ILIKE :q) ";
        $params[":q"] = "%" . $_GET["q"] . "%";
    }

    // FILTRO PROGRAMA
    if (isset($_GET["programa"]) && $_GET["programa"] !== "") {
        $sql .= " AND programa = :p";
        $params[":p"] = $_GET["programa"];
    }

    // FILTRO HORA
    if (isset($_GET["hora"]) && $_GET["hora"] !== "") {
        $sql .= " AND hora = :h";
        $params[":h"] = $_GET["hora"];
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    out(true, "", ["records" => $rows]);
}

/* ========================================
   3. TOGGLE LLEGADA (SEMAFORO)
========================================= */
if ($action === "toggle_arrival") {

    if (empty($_POST["id"])) {
        out(false, "ID faltante");
    }

    $id = $_POST["id"];

    // Obtener valor actual
    $stmt = $pdo->prepare("SELECT arrived_count FROM registros WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        out(false, "No existe el registro");
    }

    $new_value = $row["arrived_count"] == 1 ? 0 : 1;

    // Actualizar
    $upd = $pdo->prepare("UPDATE registros SET arrived_count = ? WHERE id = ?");
    $upd->execute([$new_value, $id]);

    out(true, "", ["new_value" => $new_value]);
}

/* ================================
   ACCIÓN NO RECONOCIDA
================================ */
out(false, "Acción inválida");

