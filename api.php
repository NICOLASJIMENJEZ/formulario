<?php
require_once __DIR__ . '/db.php'; // Conexión PDO corregida

header('Content-Type: application/json; charset=utf-8');

try {
    $action = $_REQUEST['action'] ?? 'list';

    // 1. LISTAR REGISTROS (con filtros opcionales)
    if ($action === 'list') {
        $sql = "SELECT * FROM registros WHERE 1=1";
        $params = [];

        if (!empty($_GET['programa'])) {
            $sql .= " AND programa = :programa";
            $params[':programa'] = $_GET['programa'];
        }

        if (!empty($_GET['q'])) {
            $q = "%" . $_GET['q'] . "%";
            $sql .= " AND (titular_nombre LIKE :q OR titular_apellidos LIKE :q OR titular_cc LIKE :q)";
            $params[':q'] = $q;
        }

        $sql .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'records' => $rows]);
        exit;
    }

    //  2. LISTAR PROGRAMAS ÚNICOS
    if ($action === 'programs') {
        $stmt = $pdo->query("SELECT DISTINCT COALESCE(NULLIF(programa,''), '__EMPTY__') AS programa FROM registros ORDER BY programa ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'programs' => $rows]);
        exit;
    }

    //  3. CREAR NUEVO REGISTRO
    if ($action === 'create') {
        $required = ['titular_nombre', 'titular_apellidos', 'titular_cc'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => "El campo $field es obligatorio."]);
                exit;
            }
        }

        $sql = "INSERT INTO registros (
            titular_nombre, titular_apellidos, titular_cc, titular_celular, titular_correo,
            hora, programa, discapacidad, discapacidad_cual,
            invitado1_nombre, invitado1_apellidos, invitado1_cc,
            invitado2_nombre, invitado2_apellidos, invitado2_cc,
            invitado3_nombre, invitado3_apellidos, invitado3_cc, arrived_count
        ) VALUES (
            :titular_nombre, :titular_apellidos, :titular_cc, :titular_celular, :titular_correo,
            :hora, :programa, :discapacidad, :discapacidad_cual,
            :invitado1_nombre, :invitado1_apellidos, :invitado1_cc,
            :invitado2_nombre, :invitado2_apellidos, :invitado2_cc,
            :invitado3_nombre, :invitado3_apellidos, :invitado3_cc, :arrived_count
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titular_nombre' => $_POST['titular_nombre'] ?? null,
            ':titular_apellidos' => $_POST['titular_apellidos'] ?? null,
            ':titular_cc' => $_POST['titular_cc'] ?? null,
            ':titular_celular' => $_POST['titular_celular'] ?? null,
            ':titular_correo' => $_POST['titular_correo'] ?? null,
            ':hora' => $_POST['hora'] ?? null,
            ':programa' => $_POST['programa'] ?? null,
            ':discapacidad' => $_POST['discapacidad'] ?? null,
            ':discapacidad_cual' => $_POST['discapacidad_cual'] ?? null,
            ':invitado1_nombre' => $_POST['invitado1_nombre'] ?? null,
            ':invitado1_apellidos' => $_POST['invitado1_apellidos'] ?? null,
            ':invitado1_cc' => $_POST['invitado1_cc'] ?? null,
            ':invitado2_nombre' => $_POST['invitado2_nombre'] ?? null,
            ':invitado2_apellidos' => $_POST['invitado2_apellidos'] ?? null,
            ':invitado2_cc' => $_POST['invitado2_cc'] ?? null,
            ':invitado3_nombre' => $_POST['invitado3_nombre'] ?? null,
            ':invitado3_apellidos' => $_POST['invitado3_apellidos'] ?? null,
            ':invitado3_cc' => $_POST['invitado3_cc'] ?? null,
            ':arrived_count' => $_POST['arrived_count'] ?? 0
        ]);

        echo json_encode(['success' => true, 'message' => 'Registro creado correctamente.']);
        exit;
    }

    //  4. ELIMINAR REGISTRO
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

    //  5. ACTUALIZAR REGISTRO
    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

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
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
