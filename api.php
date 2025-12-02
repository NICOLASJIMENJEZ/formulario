<?php
require_once __DIR__ . '/db.php'; // Debe definir $pdo (PDO conectado)

header('Content-Type: application/json; charset=utf-8');

try {
    $action = $_REQUEST['action'] ?? 'list';

    /* =====================================================
       0. TOGGLE ARRIVAL (cambiar semáforo)
       ===================================================== */
    if ($action === 'toggle_arrival') {

        $id = (int) ($_REQUEST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        // Alterna entre 0 y 1
        $sql = "UPDATE registros
                SET arrived_count = CASE 
                    WHEN COALESCE(arrived_count,0) = 1 THEN 0 
                    ELSE 1 
                END
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Consultar nuevo valor
        $stmt = $pdo->prepare("SELECT COALESCE(arrived_count,0) AS arrived_count FROM registros WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'arrived_count' => (int)$row['arrived_count']
        ]);
        exit;
    }

    /* =====================================================
       1. LISTAR REGISTROS CON FILTROS
       ===================================================== */
    if ($action === 'list') {

        $sql = "SELECT 
                    id,
                    titular_nombre,
                    titular_apellidos,
                    titular_cc,
                    programa,
                    hora,
                    COALESCE(arrived_count,0) AS arrived_count
                FROM registros
                WHERE 1=1";
        $params = [];

        // Filtro programa
        if (!empty($_GET['programa'])) {
            $sql .= " AND programa = :programa";
            $params[':programa'] = $_GET['programa'];
        }

        // Filtro búsqueda q
        if (!empty($_GET['q'])) {
            $q = "%" . $_GET['q'] . "%";
            $sql .= " AND (titular_nombre LIKE :q 
                        OR titular_apellidos LIKE :q 
                        OR titular_cc LIKE :q)";
            $params[':q'] = $q;
        }

        // Filtro hora
        if (!empty($_GET['hora'])) {
            $sql .= " AND hora = :hora";
            $params[':hora'] = $_GET['hora'];
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'records' => $rows
        ]);
        exit;
    }

    /* =====================================================
       2. LISTAR PROGRAMAS ÚNICOS
       ===================================================== */
    if ($action === 'programs') {

        $stmt = $pdo->query("SELECT DISTINCT programa FROM registros WHERE programa <> '' ORDER BY programa ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'success'   => true,
            'programs'  => $rows
        ]);
        exit;
    }

    /* =====================================================
       3. CREAR REGISTRO
       ===================================================== */
    if ($action === 'create') {

        $required = ['titular_nombre', 'titular_apellidos', 'titular_cc'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => "El campo $field es obligatorio"]);
                exit;
            }
        }

        $sql = "INSERT INTO registros (
                    titular_nombre,
                    titular_apellidos,
                    titular_cc,
                    programa,
                    hora,
                    arrived_count
                ) VALUES (
                    :titular_nombre,
                    :titular_apellidos,
                    :titular_cc,
                    :programa,
                    :hora,
                    0
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titular_nombre'   => $_POST['titular_nombre'],
            ':titular_apellidos'=> $_POST['titular_apellidos'],
            ':titular_cc'       => $_POST['titular_cc'],
            ':programa'         => $_POST['programa'] ?? null,
            ':hora'             => $_POST['hora'] ?? null
        ]);

        echo json_encode(['success' => true, 'message' => 'Registro creado']);
        exit;
    }

    /* =====================================================
       4. ELIMINAR
       ===================================================== */
    if ($action === 'delete') {

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM registros WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Registro eliminado']);
        exit;
    }

    /* =====================================================
       5. UPDATE
       ===================================================== */
    if ($action === 'update') {

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $allowed = ['titular_nombre', 'titular_apellidos', 'titular_cc', 'programa', 'hora'];

        $updates = [];
        $values  = [];

        foreach ($allowed as $campo) {
            if (isset($_POST[$campo])) {
                $updates[]  = "$campo = ?";
                $values[]   = $_POST[$campo];
            }
        }

        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'Sin datos para actualizar']);
            exit;
        }

        $values[] = $id;

        $sql = "UPDATE registros SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        echo json_encode(['success' => true, 'message' => 'Registro actualizado']);
        exit;
    }

    /* =====================================================
       SI NO CALZA NADA
       ===================================================== */
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);

} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'message' => 'Error servidor: ' . $e->getMessage()
    ]);
}


