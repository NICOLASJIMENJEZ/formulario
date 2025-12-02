<?php
require_once __DIR__ . '/db.php'; // db.php está en la misma carpeta

header('Content-Type: application/json; charset=utf-8');

try {
    $action = $_REQUEST['action'] ?? 'list';

    /* ==============================
       RESPUESTA JSON
    ============================== */
    function out($ok, $msg = "", $extra = []) {
        echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
        exit;
    }

    /* ==============================
       1. LISTAR REGISTROS
    ============================== */
    if ($action === 'list') {
        $sql = "SELECT id, titular_nombre, titular_apellidos, titular_cc, titular_celular, titular_correo,
                       hora, programa, discapacidad, arrived_count
                FROM registros
                WHERE 1=1";
        $params = [];

        // Búsqueda general
        if (!empty($_GET['q'])) {
            $sql .= " AND (titular_nombre ILIKE :q OR titular_apellidos ILIKE :q OR titular_cc ILIKE :q)";
            $params[':q'] = "%" . $_GET['q'] . "%";
        }

        // Filtro programa
        if (isset($_GET['programa']) && $_GET['programa'] !== "") {
            $sql .= " AND (programa = :programa OR programa IS NULL)";
            $params[':programa'] = $_GET['programa'];
        }

        // Filtro hora
        if (isset($_GET['hora']) && $_GET['hora'] !== "") {
            $sql .= " AND (hora = :hora OR hora IS NULL)";
            $params[':hora'] = $_GET['hora'];
        }

        $sql .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        out(true, "", ['records' => $rows]);
    }

    /* ==============================
       2. LISTAR PROGRAMAS
    ============================== */
    if ($action === 'programs') {
        $stmt = $pdo->query("SELECT DISTINCT COALESCE(programa, '__VACIO__') AS programa FROM registros ORDER BY programa ASC");
        $programs = $stmt->fetchAll(PDO::FETCH_COLUMN);
        out(true, "", ['programs' => $programs]);
    }

    /* ==============================
       3. TOGGLE LLEGADA (SEMAFORO)
    ============================== */
    if ($action === 'toggle_arrival') {
        $id = $_POST['id'] ?? null;
        if (!$id) out(false, "ID faltante");

        // Obtener valor actual
        $stmt = $pdo->prepare("SELECT arrived_count FROM registros WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) out(false, "Registro no encontrado");

        $new_value = $row['arrived_count'] == 1 ? 0 : 1;

        // Actualizar
        $upd = $pdo->prepare("UPDATE registros SET arrived_count = ? WHERE id = ?");
        $upd->execute([$new_value, $id]);

        out(true, "", ['new_value' => $new_value]);
    }

    /* ==============================
       4. ELIMINAR REGISTRO
    ============================== */
    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) out(false, "ID inválido");

        $stmt = $pdo->prepare("DELETE FROM registros WHERE id = ?");
        $stmt->execute([$id]);
        out(true, "Registro eliminado correctamente");
    }

    /* ==============================
       5. ACTUALIZAR REGISTRO
    ============================== */
    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) out(false, "ID inválido");

        $allowed = [
            'titular_nombre','titular_apellidos','titular_cc','titular_celular','titular_correo',
            'hora','programa','discapacidad','arrived_count',
            'invitado1_nombre','invitado1_cc','invitado1_discapacidad',
            'invitado2_nombre','invitado2_cc','invitado2_discapacidad',
            'invitado3_nombre','invitado3_cc','invitado3_discapacidad'
        ];

        $updates = [];
        $values = [];
        foreach ($allowed as $campo) {
            if (isset($_POST[$campo])) {
                $updates[] = "$campo = ?";
                $values[] = $_POST[$campo];
            }
        }

        if (empty($updates)) out(false, "No hay campos para actualizar");

        $values[] = $id;
        $sql = "UPDATE registros SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        out(true, "Registro actualizado correctamente");
    }

    out(false, "Acción no válida");

} catch (Throwable $e) {
    out(false, "Error del servidor: " . $e->getMessage());
}

