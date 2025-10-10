<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

require_once __DIR__ . '/../db.php';

function respond($success, $data = []) {
    @ob_end_clean();
    echo json_encode(array_merge(['success' => $success], is_array($data) ? $data : ['message' => $data]));
    exit;
}

try {
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';

    // Helper: read file storage
    $dataFile = __DIR__ . '/../data/records.json';

    if ($action === 'list') {
        // Optional server-side filters
        $filterProgram = isset($_REQUEST['programa']) ? trim((string)$_REQUEST['programa']) : null;
        $filterQ = isset($_REQUEST['q']) ? trim((string)$_REQUEST['q']) : null;

        if (isset($USE_FILE_STORAGE) && $USE_FILE_STORAGE === true) {
            if (!file_exists($dataFile)) respond(true, ['records' => []]);
            $json = file_get_contents($dataFile);
            $arr = json_decode($json, true) ?: [];
            // apply filters in file mode
            $filtered = array_values(array_filter($arr, function($item) use ($filterProgram, $filterQ) {
                if ($filterProgram !== null) {
                    if ($filterProgram === '__EMPTY__') {
                        if (isset($item['programa']) && $item['programa'] !== '') return false;
                    } else {
                        if (!isset($item['programa']) || $item['programa'] !== $filterProgram) return false;
                    }
                }
                if ($filterQ) {
                    $q = mb_strtolower($filterQ);
                    $name = mb_strtolower(trim(($item['titular_nombre'] ?? '') . ' ' . ($item['titular_apellidos'] ?? '')));
                    $ced = mb_strtolower($item['titular_cc'] ?? '');
                    if (mb_strpos($name, $q) === false && mb_strpos($ced, $q) === false) return false;
                }
                return true;
            }));
            respond(true, ['records' => $filtered]);
        }

        if (isset($pdo) && $pdo instanceof PDO) {
            // Build SQL with optional filters
            $sql = 'SELECT * FROM registros';
            $w = [];
            $params = [];
            if ($filterProgram !== null) {
                if ($filterProgram === '__EMPTY__') {
                    $w[] = "(programa IS NULL OR programa = '')";
                } else {
                    $w[] = 'programa = ?';
                    $params[] = $filterProgram;
                }
            }
            if ($filterQ) {
                $w[] = '(LOWER(CONCAT_WS(" ", titular_nombre, titular_apellidos)) LIKE ? OR LOWER(titular_cc) LIKE ?)';
                $params[] = '%' . mb_strtolower($filterQ) . '%';
                $params[] = '%' . mb_strtolower($filterQ) . '%';
            }
            if ($w) $sql .= ' WHERE ' . implode(' AND ', $w);
            $sql .= ' ORDER BY id DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            respond(true, ['records' => $rows]);
        }

        respond(false, 'No hay método de almacenamiento disponible');
    }

    if ($action === 'delete') {
        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        if (!$id) respond(false, 'ID inválido');

        if (isset($USE_FILE_STORAGE) && $USE_FILE_STORAGE === true) {
            if (!file_exists($dataFile)) respond(false, 'Archivo de datos no existe');
            $arr = json_decode(file_get_contents($dataFile), true) ?: [];
            $found = false;
            foreach ($arr as $k => $item) {
                if (isset($item['id']) && (int)$item['id'] === $id) {
                    unset($arr[$k]);
                    $found = true;
                    break;
                }
            }
            if (!$found) respond(false, 'Registro no encontrado');
            // Reindex array
            $arr = array_values($arr);
            file_put_contents($dataFile, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            respond(true, 'Registro eliminado');
        }

        if (isset($pdo) && $pdo instanceof PDO) {
            $stmt = $pdo->prepare('DELETE FROM registros WHERE id = ?');
            $stmt->execute([$id]);
            respond(true, 'Registro eliminado');
        }

        respond(false, 'No hay método de almacenamiento disponible');
    }

    if ($action === 'programs') {
        // Return distinct program names for populating filters
        if (isset($USE_FILE_STORAGE) && $USE_FILE_STORAGE === true) {
            if (!file_exists($dataFile)) respond(true, ['programs' => []]);
            $arr = json_decode(file_get_contents($dataFile), true) ?: [];
            $set = [];
            $hasEmpty = false;
            foreach ($arr as $it) {
                $p = isset($it['programa']) ? trim((string)$it['programa']) : '';
                if ($p === '') { $hasEmpty = true; continue; }
                $set[$p] = true;
            }
            $list = array_keys($set);
            sort($list);
            if ($hasEmpty) array_unshift($list, '__EMPTY__');
            respond(true, ['programs' => $list]);
        }

        if (isset($pdo) && $pdo instanceof PDO) {
            $stmt = $pdo->query("SELECT DISTINCT TRIM(programa) AS programa FROM registros ORDER BY programa ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            // detect empty
            $emptyStmt = $pdo->query("SELECT COUNT(*) FROM registros WHERE programa IS NULL OR programa = ''");
            $emptyCount = (int)$emptyStmt->fetchColumn();
            $list = array_values(array_filter($rows, function($v){ return $v !== null && $v !== ''; }));
            if ($emptyCount > 0) array_unshift($list, '__EMPTY__');
            respond(true, ['programs' => $list]);
        }

        respond(true, ['programs' => []]);
    }

    if ($action === 'update') {
        // Allowed fields to update (whitelist)
        $allowed = [
            'titular_nombre','titular_apellidos','titular_cc','titular_celular','titular_correo',
            'hora','programa','discapacidad','discapacidad_cual','fecha_hora',
            'invitado1_nombre','invitado1_apellidos','invitado1_cc','invitado1_discapacidad',
            'invitado2_nombre','invitado2_apellidos','invitado2_cc','invitado2_discapacidad',
            'invitado3_nombre','invitado3_apellidos','invitado3_cc','invitado3_discapacidad'
        ];
        // allow arrived count (manual semaforo control)
        $allowed[] = 'arrived_count';

        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        if (!$id) respond(false, 'ID inválido');

        $updateData = [];
        foreach ($allowed as $k) {
            if (isset($_REQUEST[$k])) {
                $updateData[$k] = trim((string)$_REQUEST[$k]);
            }
        }

        if (empty($updateData)) respond(false, 'No hay campos para actualizar');

        if (isset($USE_FILE_STORAGE) && $USE_FILE_STORAGE === true) {
            if (!file_exists($dataFile)) respond(false, 'Archivo de datos no existe');
            $arr = json_decode(file_get_contents($dataFile), true) ?: [];
            $found = false;
            foreach ($arr as $k => $item) {
                if (isset($item['id']) && (int)$item['id'] === $id) {
                    foreach ($updateData as $uk => $uv) $arr[$k][$uk] = $uv;
                    $found = true;
                    break;
                }
            }
            if (!$found) respond(false, 'Registro no encontrado');
            file_put_contents($dataFile, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            respond(true, 'Registro actualizado');
        }

        if (isset($pdo) && $pdo instanceof PDO) {
            // Ensure we only update columns that exist in the DB to avoid 'Unknown column' errors.
            try {
                $colsStmt = $pdo->query("SHOW COLUMNS FROM registros");
                $dbCols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
            } catch (Throwable $e) {
                $dbCols = null;
            }

            // If arrived_count is requested but not present, attempt to add it (non-fatal)
            if (array_key_exists('arrived_count', $updateData) && is_array($dbCols) && !in_array('arrived_count', $dbCols)) {
                try {
                    $pdo->exec("ALTER TABLE registros ADD COLUMN arrived_count TINYINT(1) DEFAULT 0 AFTER fecha_hora");
                    // refresh columns
                    $colsStmt = $pdo->query("SHOW COLUMNS FROM registros");
                    $dbCols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
                } catch (Throwable $e) {
                    // log but don't fail the whole request; just drop the column from updateData so update works
                    file_put_contents(__DIR__ . '/../data/debug.log', date('[Y-m-d H:i:s] ') . "Failed to add arrived_count column: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
                    unset($updateData['arrived_count']);
                }
            }

            // Filter updateData to columns present in DB (if we could fetch them)
            if (is_array($dbCols)) {
                foreach ($updateData as $col => $val) {
                    if (!in_array($col, $dbCols)) {
                        // drop unknown columns to avoid SQL errors
                        unset($updateData[$col]);
                    }
                }
            }

            if (empty($updateData)) {
                // Nothing left to update (e.g. only unknown columns were provided)
                respond(true, 'No hay campos actualizables o la columna no existe en la base de datos; cambios no realizados en BD.');
            }

            $sets = [];
            $values = [];
            foreach ($updateData as $col => $val) {
                $sets[] = "$col = ?";
                $values[] = $val;
            }
            $values[] = $id;
            $sql = 'UPDATE registros SET ' . implode(',', $sets) . ' WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            respond(true, 'Registro actualizado');
        }

        respond(false, 'No hay método de almacenamiento disponible');
    }

    respond(false, 'Acción no soportada');

} catch (Throwable $e) {
    @ob_end_clean();
    http_response_code(500);
    file_put_contents(__DIR__ . '/../data/debug.log', date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
    exit;
}
