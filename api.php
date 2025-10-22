<?php
require_once __DIR__ . '/db.php'; // db.php está en la misma carpeta

header('Content-Type: application/json; charset=utf-8');

try {
    $action = $_REQUEST['action'] ?? 'list';

    // 📋 1. LISTAR REGISTROS (con filtros opcionales)
    if ($action === 'list') {
        $sql = "SELECT * FROM registros WHERE 1=1";
        $params = [];

        // Filtro por programa (si se envía)
        if (!empty($_GET['programa'])) {
            $sql .= " AND programa = :programa";
            $params[':programa'] = $_GET['programa'];
        }

        // Búsqueda general por nombre, apellido o cédula
        if (!empty($_GET['q'])) {
            $q = "%" . $_GET['q'] . "%";
            $sql .= " AND (titular_nombre LIKE :q OR titular_apellidos LIKE :q OR titular_cc LIKE :q)";
            $params[':q'] = $q;
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

    // 📚 2. LISTAR PROGRAMAS ÚNICOS (para llenar el <select>)
    if ($action === 'programs') {
        $stmt = $pdo->query("SELECT DISTINCT COALESCE(NULLIF(programa,''), '__EMPTY__') AS programa FROM registros ORDER BY programa ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'programs' => $rows]);
        exit;
    }

    // ❌ 3. ELIMINAR REGISTRO
    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM registros WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => "Registro eliminado correctamente."]);
        exit;
    }

    // ✏️ 4. ACTUALIZAR REGISTRO
    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        // Campos permitidos para actualización
   $allowed = [
    'titular_nombre', 'titular_apellidos', 'titular_cc', 'titular_celular', 'titular_correo',
    'hora', 'programa', 'discapacidad', 'discapacidad_cual',
    'invitado1_nombre', 'invitado1_apellidos', 'invitado1_cc',
    'invitado2_nombre', 'invitado2_apellidos', 'invitado2_cc',
    'invitado3_nombre', 'invitado3_apellidos', 'invitado3_cc',
    'arrived_count'
];

        $updates = [];
        $values = [];
        foreach ($allowed as $campo) {
            if (isset($_POST[$campo])) {
                $updates[] = "$campo = ?";
                $values[] = $_POST[$campo];
            }
        }

        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'No hay campos para actualizar.']);
            exit;
        }

        $values[] = $id;
        $sql = "UPDATE registros SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        echo json_encode(['success' => true, 'message' => 'Registro actualizado correctamente.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
