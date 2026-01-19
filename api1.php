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
        // Primero verificar si la columna program_arrival existe
        try {
            $pdo->exec("ALTER TABLE registros ADD COLUMN IF NOT EXISTS program_arrival SMALLINT DEFAULT 0");
        } catch (Exception $e) {
            // Columna ya existe, continuar
        }

        $sql = "SELECT id, titular_nombre, titular_apellidos, titular_cc, titular_celular, titular_correo,
                       hora, programa, discapacidad, 
                       COALESCE(arrived_count, 0) as arrived_count,
                       COALESCE(program_arrival, 0) as program_arrival
                FROM registros
                WHERE 1=1";
        $params = [];

        // Búsqueda general
        if (!empty($_GET['q'])) {
            $sql .= " AND (titular_nombre ILIKE :q OR titular_apellidos ILIKE :q OR titular_cc ILIKE :q)";
            $params[':q'] = "%" . $_GET['q'] . "%";
        }

        // Filtro programa
        if (isset($_GET['programa']) && $_GET['programa'] !== "" && $_GET['programa'] !== '__VACIO__') {
            $sql .= " AND programa = :programa";
            $params[':programa'] = $_GET['programa'];
        }

        // Filtro hora
        if (isset($_GET['hora']) && $_GET['hora'] !== "") {
            $sql .= " AND hora = :hora";
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

    /* ============================================
       3. TOGGLE LLEGADA (TITULAR + GRADUADOS)
       Sistema original - actualiza por titular_cc
    ============================================ */
    if ($action === 'toggle_arrival') {

        $id = $_POST['id'] ?? null;
        if (!$id) out(false, "ID faltante");

        // Obtener titular_cc del registro
        $stmt = $pdo->prepare("SELECT titular_cc, COALESCE(arrived_count, 0) as arrived_count FROM registros WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) out(false, "Registro no encontrado");

        $cc = $row["titular_cc"];
        $new_value = $row['arrived_count'] == 1 ? 0 : 1;

        // Actualizar TODOS los que tengan el mismo titular_cc
        $upd = $pdo->prepare("
            UPDATE registros
            SET arrived_count = ?
            WHERE titular_cc = ?
        ");
        $upd->execute([$new_value, $cc]);

        out(true, "Llegadas actualizadas", ["new_value" => $new_value]);
    }

    /* ============================================
       3B. TOGGLE PROGRAM ARRIVAL (NUEVO)
       Sistema independiente para control de programas
       Solo actualiza el registro individual
    ============================================ */
    if ($action === 'toggle_program_arrival') {

        $id = $_POST['id'] ?? null;
        if (!$id) out(false, "ID faltante");

        // Verificar/crear columna program_arrival
        try {
            $pdo->exec("ALTER TABLE registros ADD COLUMN IF NOT EXISTS program_arrival SMALLINT DEFAULT 0");
        } catch (Exception $e) {
            // Ya existe, continuar
        }

        // Obtener valor actual de program_arrival
        $stmt = $pdo->prepare("SELECT id, COALESCE(program_arrival, 0) as program_arrival FROM registros WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) out(false, "Registro no encontrado");

        $current = $row['program_arrival'];

        // Alternar: 0 -> 1, 1 -> 0
        $new_value = ($current == 1) ? 0 : 1;

        // Actualizar SOLO este registro individual (NO por titular_cc)
        $upd = $pdo->prepare("UPDATE registros SET program_arrival = ? WHERE id = ?");
        $success = $upd->execute([$new_value, $id]);

        if ($success) {
            out(true, $new_value == 1 ? "Marcado como llegado" : "Desmarcado", ["new_value" => $new_value]);
        } else {
            out(false, "Error al actualizar");
        }
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
?>
