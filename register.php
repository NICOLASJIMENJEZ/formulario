<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

function is_ajax_request() {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') return true;
    if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) return true;
    if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') return true;
    return false;
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
        if (is_ajax_request()) respond_json(false, 'Método no permitido');
        respond_redirect(false, 'Método no permitido');
    }

    // ✅ Solo los campos existentes en tu tabla
    $fields = [
        'titular_nombre','titular_apellidos','titular_cc','titular_celular','titular_correo',
        'hora','programa','discapacidad','discapacidad_cual','fecha_hora',
        'invitado1_nombre','invitado1_apellidos','invitado1_cc',
        'invitado2_nombre','invitado2_apellidos','invitado2_cc',
        'invitado3_nombre','invitado3_apellidos','invitado3_cc'
    ];

    $record = [];
    foreach ($fields as $f) {
        $record[$f] = isset($_POST[$f]) ? trim((string)$_POST[$f]) : null;
    }

    if (empty($record['titular_nombre']) || empty($record['titular_apellidos'])) {
        if (is_ajax_request()) respond_json(false, 'Nombre y apellidos del titular son obligatorios.');
        respond_redirect(false, 'Nombre y apellidos del titular son obligatorios.');
    }

    if (empty($record['fecha_hora'])) $record['fecha_hora'] = date('Y-m-d H:i:s');
    if (!isset($record['discapacidad'])) $record['discapacidad'] = 'no';
    if (!isset($record['arrived_count'])) $record['arrived_count'] = 0;

    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) @mkdir($dataDir, 0755, true);

    // 📂 Fallback a JSON si no hay DB
    if (isset($USE_FILE_STORAGE) && $USE_FILE_STORAGE === true) {
        $file = $dataDir . '/records.json';
        $fp = fopen($file, 'c+');
        if (!$fp) throw new Exception('No se puede abrir el archivo de datos.');
        flock($fp, LOCK_EX);
        rewind($fp);
        $contents = stream_get_contents($fp);
        $arr = $contents ? json_decode($contents, true) : [];
        if (!is_array($arr)) $arr = [];
        $max = 0; foreach ($arr as $it) if (isset($it['id'])) $max = max($max, (int)$it['id']);
        $record['id'] = $max + 1;
        $arr[] = $record;
        ftruncate($fp, 0); rewind($fp);
        fwrite($fp, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); fflush($fp);
        flock($fp, LOCK_UN); fclose($fp);
        if (is_ajax_request()) respond_json(true, ['id' => $record['id']]);
        respond_redirect(true, 'Registro guardado (ID ' . $record['id'] . ')');
    }

    // 🗄️ Modo base de datos (Render)
    if (isset($pdo) && $pdo instanceof PDO) {
        $cols = array_keys($record);
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $cols_sql = implode(',', $cols);
        $stmt = $pdo->prepare("INSERT INTO registros ($cols_sql) VALUES ($placeholders)");
        $vals = array_map(fn($v) => $v === null ? null : $v, array_values($record));
        $stmt->execute($vals);
        $id = $pdo->lastInsertId();
        if (is_ajax_request()) respond_json(true, ['id' => $id]);
        respond_redirect(true, 'Registro guardado (ID ' . $id . ')');
    }

    throw new Exception('No hay método de almacenamiento disponible.');

} catch (Throwable $e) {
    $log = __DIR__ . '/data/debug.log';
    @file_put_contents($log, date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, FILE_APPEND);
    if (is_ajax_request()) {
        http_response_code(500);
        respond_json(false, 'Error del servidor: ' . $e->getMessage());
    }
    respond_redirect(false, 'Error del servidor: ' . $e->getMessage());
}
?>
