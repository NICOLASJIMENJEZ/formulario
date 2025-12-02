<?php
require_once __DIR__ . '/db.php'; // debes tener $pdo definido aquí

header('Content-Type: application/json; charset=utf-8');

try {
    $action = $_REQUEST['action'] ?? 'list';

    /* ================================
       0. TOGGLE ARRIVAL (marcar/desmarcar llegada)
       Endpoint: api.php?action=toggle_arrival&id=123  (GET or POST)
       Devuelve: { success: true, arrived_count: 0|1 }
       ================================ */
    if ($action === 'toggle_arrival') {
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        // Alternamos 0 <-> 1 de forma segura en SQL
        $sql = "UPDATE registros
                SET arrived_count = CASE WHEN COALESCE(arrived_count,0) = 1 THEN 0 ELSE 1 END
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Devolvemos el nuevo valor
        $stmt2 = $pdo->prepare("SELECT COALESCE(arrived_count,0) + 0 AS arrived_count FROM registros WHERE id = :id");
        $stmt2->execute([':id' => $id]);
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
        $val = isset($row['arrived_count']) ? (int)$row['arrived_count'] : 0;

        echo json_encode(['success' => true, 'arrived_count' => $val]);
        exit;
    }

    /* ================================
       1. LISTAR REGISTROS (CON FILTROS)
       ================================ */
    if ($action === 'list') {

        // Seleccionamos todo y forzamos arrived_count como número (0 o 1)
        // COALESCE(...)+0 convierte NULL a 0 y devuelve valor numérico
        $sql = "SELECT *,
                       (COALESCE(arrived_count, 0) + 0) AS arrived_count
                FROM registros
                WHERE 1=1";
        $params = [];

        // Filtro por programa
        if (!empty($_GET['programa'])) {
            $sql .= " AND programa = :programa";
            $params[':programa'] = $_GET['programa'];
        }

        // Filtro por búsqueda
        if (!empty($_GET['q'])) {
            $q = "%" . $_GET['q'] . "%";
            $sql .= " AND (titular_nombre LIKE :q 
                        OR titular_apellidos LIKE :q 
                        OR titular_cc LIKE :q)";
            $params[':q'] = $q;
        }

        // Filtro por hora
        if (!empty($_GET['hora'])) {
            // Si en tu DB hora es TIME y mandas "9:30" podrías necesitar normalizar.
            // Aquí asumimos que guardas horas como strings iguales a las opciones del front.
            $sql .= " AND hora = :hora";
            $params[':hora'] = $_GET['hora'];
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Aseguramos arrived_count como entero en el resultado (por si acaso)
        foreach ($rows as &$r) {
            $r['arrived_count'] = isset($r['arrived_count']) ? (int)$r['arrived_count'] : 0;
        }
        unset($r);

        echo json_encode(['success' => true, 'records' => $rows]);
        exit;
    }

    /* ================================
       2. LISTAR PROGRAMAS ÚNICOS
       ================================ */
    if ($action === 'programs') {
        $stmt = $pdo->query("SELECT DISTINCT COALESCE(NULLIF(programa,''), '__EMPTY__') AS programa FROM registros ORDER BY programa ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'programs' => $rows]);
        exit;
    }

    /* ================================
       3. CREAR NUEVO REGISTRO
       ================================ */
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
            invitado3_nombre, invitado3_apellidos, invitado3_cc,
            arrived_count
        ) VALUES (
            :titular_nombre, :titular_apellidos, :titular_cc, :titular_celular, :titular_correo,
            :hora, :programa, :discapacidad, :discapacidad_cual,
            :invitado1_nombre, :invitado1_apellidos, :invitado1_cc,
            :invitado2_nombre, :invitado2_apellidos, :invitado2_cc,
            :invitado3_nombre, :invitado3_apellidos, :invitado3_cc,
            :arrived_count
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

    /* ================================
       4. ELIMINAR REGISTRO
       ================================ */
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

    /* ================================
       5. ACTUALIZAR REGISTRO
       ================================ */
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

