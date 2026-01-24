<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

function is_ajax_request() {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') return true;
    if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) return true;
    if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') return true;
    // NUEVO: Detectar si Content-Type es JSON
    if (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) return true;
    return true; // SIEMPRE devolver JSON para fetch()
}

function respond_json($success, $payload = []) {
    echo json_encode(array_merge(['success' => $success], is_array($payload) ? $payload : ['message' => $payload]));
    exit;
}

function respond_redirect($success, $message) {
    $type = $success ? 'success' : 'danger';
    $url = './index.php?msg=' . urlencode($message) . '&type=' . $type;
    header('Location: ' . $url);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond_json(false, ['message' => 'Método no permitido', 'type' => 'danger']);
    }

    // Campos existentes en tu tabla
    $fields = [
        'titular_nombre','titular_apellidos','titular_cc','titular_celular','titular_correo',
        'hora','programa','discapacidad','discapacidad_cual',
        'invitado1_nombre','invitado1_apellidos','invitado1_cc','invitado1_fecha_nacimiento','invitado1_fecha_expedicion',
        'invitado2_nombre','invitado2_apellidos','invitado2_cc','invitado2_fecha_nacimiento','invitado2_fecha_expedicion',
        'invitado3_nombre','invitado3_apellidos','invitado3_cc','invitado3_fecha_nacimiento','invitado3_fecha_expedicion'
    ];

    $record = [];
    foreach ($fields as $f) {
        $val = isset($_POST[$f]) ? trim((string)$_POST[$f]) : null;
        $record[$f] = ($val === '' || $val === null) ? null : $val;
    }

    // Validaciones
    if (empty($record['titular_nombre']) || empty($record['titular_apellidos'])) {
        respond_json(false, ['message' => 'Nombre y apellidos del graduado son obligatorios', 'type' => 'danger']);
    }

    if (empty($record['hora']) || empty($record['programa'])) {
        respond_json(false, ['message' => 'Hora y programa son obligatorios', 'type' => 'danger']);
    }

    // Valores por defecto
    if (!isset($record['discapacidad']) || $record['discapacidad'] === null) {
        $record['discapacidad'] = 'no';
    }

    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) @mkdir($dataDir, 0755, true);

    // Fallback a JSON si no hay DB
    if (isset($USE_FILE_STORAGE) && $USE_FILE_STORAGE === true) {
        $file = $dataDir . '/records.json';
        $fp = fopen($file, 'c+');
        if (!$fp) throw new Exception('No se puede abrir el archivo de datos.');
        flock($fp, LOCK_EX);
        rewind($fp);
        $contents = stream_get_contents($fp);
        $arr = $contents ? json_decode($contents, true) : [];
        if (!is_array($arr)) $arr = [];
        $max = 0;
        foreach ($arr as $it) {
            if (isset($it['id'])) $max = max($max, (int)$it['id']);
        }
        $record['id'] = $max + 1;
        $record['fecha_hora'] = date('Y-m-d H:i:s');
        $record['arrived_count'] = 0;
        $arr[] = $record;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        respond_json(true, ['message' => '¡Registro exitoso! Tu inscripción ha sido guardada correctamente.', 'type' => 'success', 'id' => $record['id']]);
    }

    // Modo base de datos (PostgreSQL en Render)
    if (isset($pdo) && $pdo instanceof PDO) {
        $cols = array_keys($record);
        $placeholders = ':' . implode(', :', $cols);
        $cols_sql = implode(', ', $cols);
        
        $sql = "INSERT INTO registros ($cols_sql) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        
        // Bind de parámetros
        $params = [];
        foreach ($record as $key => $value) {
            $params[':' . $key] = $value;
        }
        
        $stmt->execute($params);
        $id = $pdo->lastInsertId();
        
        respond_json(true, [
            'message' => '¡Registro exitoso! Tu inscripción ha sido guardada correctamente.',
            'type' => 'success',
            'id' => $id
        ]);
    }

    throw new Exception('No hay método de almacenamiento disponible.');

} catch (PDOException $e) {
    // Log del error
    $log = __DIR__ . '/data/debug.log';
    @file_put_contents($log, date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, FILE_APPEND);
    
    // Detectar cédula duplicada
    $errorMsg = $e->getMessage();
    if (strpos($errorMsg, 'unique_titular_cc') !== false || 
        strpos($errorMsg, 'duplicate key') !== false ||
        strpos($errorMsg, 'Unique violation') !== false) {
        respond_json(false, [
            'message' => '⚠️ Esta cédula ya está registrada. Si necesitas actualizar tu información, contacta con la administración.',
            'type' => 'warning'
        ]);
    }
    
    // Otro error de base de datos
    respond_json(false, [
        'message' => 'Error al guardar el registro. Por favor, intenta nuevamente.',
        'type' => 'danger'
    ]);
    
} catch (Throwable $e) {
    // Log del error
    $log = __DIR__ . '/data/debug.log';
    @file_put_contents($log, date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, FILE_APPEND);
    
    respond_json(false, [
        'message' => $e->getMessage(),
        'type' => 'danger'
    ]);
}
?> 
