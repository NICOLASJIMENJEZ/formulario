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

        // Filtro por hora (nuevo - para el sistema de programas)
        if (!empty($_GET['hora'])) {
            $sql .= " AND hora = :hora";
            $params[':hora'] = $_GET['hora'];
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

        // Asegurar que program_arrival existe en la respuesta
        foreach ($rows as &$row) {
            if (!isset($row['program_arrival'])) {
                $row['program_arrival'] = 0;
            }
        }

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
            'hora', 'programa', 'discapacidad',
            'invitado1_nombre', 'invitado1_cc', 'invitado1_discapacidad',
            'invitado2_nombre', 'invitado2_cc', 'invitado2_discapacidad',
            'invitado3_nombre', 'invitado3_cc', 'invitado3_discapacidad',
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

    // 🚦 5. TOGGLE ARRIVAL - SISTEMA DE PROGRAMAS (NUEVO - no interfiere con arrived_count)
    if ($action === 'toggle_arrival') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        // Verificar si la columna program_arrival existe, si no, crearla
        try {
            // Para MySQL/MariaDB
            $pdo->exec("ALTER TABLE registros ADD COLUMN IF NOT EXISTS program_arrival TINYINT DEFAULT 0");
        } catch (Exception $e) {
            // Si falla (puede ser que ya exista o sintaxis diferente), intentar consulta simple
            try {
                $pdo->query("SELECT program_arrival FROM registros LIMIT 1");
            } catch (Exception $e2) {
                // La columna no existe y no pudimos crearla con IF NOT EXISTS, intentar sin IF NOT EXISTS
                try {
                    $pdo->exec("ALTER TABLE registros ADD COLUMN program_arrival TINYINT DEFAULT 0");
                } catch (Exception $e3) {
                    // Ya existe o hay otro problema, continuar
                }
            }
        }

        // Obtener valor actual de program_arrival
        $stmt = $pdo->prepare("SELECT COALESCE(program_arrival, 0) as program_arrival FROM registros WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();

        // Alternar: 0 -> 1, 1 -> 0
        $newValue = ($current == 1) ? 0 : 1;

        // Actualizar SOLO program_arrival (NO afecta arrived_count)
        $stmt = $pdo->prepare("UPDATE registros SET program_arrival = ? WHERE id = ?");
        $success = $stmt->execute([$newValue, $id]);

        if ($success) {
            echo json_encode([
                'success' => true, 
                'new_value' => $newValue,
                'message' => $newValue == 1 ? 'Marcado como llegado al programa' : 'Desmarcado'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado.']);
        }
        exit;
    }

    // Si no coincide con ninguna acción
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
