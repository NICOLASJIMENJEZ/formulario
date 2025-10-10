<?php
header('Content-Type: application/json; charset=utf-8');

// Iniciar buffer para evitar que HTML se mezcle con JSON
ob_start();

// Manejo de errores como excepciones
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Conexión o modo de almacenamiento (archivo o base de datos)
require_once __DIR__ . '/db.php'; // Debe definir $USE_FILE_STORAGE y opcionalmente $pdo

// Verifica si la petición es AJAX o espera JSON
function is_ajax_request() {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') return true;
    if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) return true;
    if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') return true;
    return false;
}

// Función para responder y salir correctamente
function respond_and_exit($success, $message) {
    @ob_end_clean();
    if (is_ajax_request()) {
        echo json_encode(['success' => $success, 'message' => $message]);
    } else {
        $type = $success ? 'success' : 'danger';
        $url = './index.php?msg=' . urlencode($message) . '&type=' . $type;
        header('Location: ' . $url);
    }
    exit;
}

// Función para acceder a datos POST con limpieza
function post($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : null;
}

try {
    // Datos del titular
    $t_nombre    = post('titular_nombre');
    $t_apellidos = post('titular_apellidos');
    $t_cc        = post('titular_cc');
    $t_celular   = post('titular_celular');
    $t_correo    = post('titular_correo');
    $hora        = post('hora');
    $programa    = post('programa');
    $discapacidad = (isset($_POST['discapacidad']) && $_POST['discapacidad'] === 'si') ? 'si' : 'no';
    $discapacidad_cual = post('discapacidad_cual');

    if (!$t_nombre || !$t_apellidos) {
        respond_and_exit(false, 'Nombre y apellidos del titular son obligatorios.');
    }

    // Recoger hasta 3 invitados
    $invitados = [];
    for ($i = 1; $i <= 3; $i++) {
        $invitados[$i] = [
            'nombre'    => post("invitado{$i}_nombre"),
            'apellidos' => post("invitado{$i}_apellidos"),
            'cc'        => post("invitado{$i}_cc"),
        ];
    }

    // Crear registro final
    $record = [
        'titular_nombre' => $t_nombre,
        'titular_apellidos' => $t_apellidos,
        'titular_cc' => $t_cc,
        'titular_celular' => $t_celular,
        'titular_correo' => $t_correo,
        'hora' => $hora,
        'programa' => $programa,
        'discapacidad' => $discapacidad,
        'discapacidad_cual' => $discapacidad_cual,
        'fecha_hora' => date('Y-m-d H:i:s'),
    ];

    // Agregar invitados al registro
    foreach ($invitados as $index => $invitado) {
        $record["invitado{$index}_nombre"] = $invitado['nombre'];
        $record["invitado{$index}_apellidos"] = $invitado['apellidos'];
        $record["invitado{$index}_cc"] = $invitado['cc'];
    }

    // === ALMACENAMIENTO EN ARCHIVO JSON ===
    if (isset($USE_FILE_STORAGE) && $USE_FILE_STORAGE === true) {
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);
        $file = $dataDir . '/records.json';

        $fp = fopen($file, 'c+');
        if (!$fp) throw new Exception('No se puede abrir el archivo de datos.');

        flock($fp, LOCK_EX);
        rewind($fp);
        $contents = stream_get_contents($fp);
        $arr = [];

        if ($contents) {
            $decoded = json_decode($contents, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $arr = $decoded;
            }
        }

        $lastId = 0;
        foreach ($arr as $item) {
            if (isset($item['id']) && is_numeric($item['id'])) {
                $lastId = max($lastId, (int)$item['id']);
            }
        }

        $newId = $lastId + 1;
        $record['id'] = $newId;
        $arr[] = $record;   

        // Guardar archivo
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        respond_and_exit(true, 'Registro guardado correctamente (ID ' . $newId . ')');
    }

    // === ALMACENAMIENTO EN BASE DE DATOS (PDO) ===
    if (isset($pdo) && $pdo instanceof PDO) {
        $columns = array_keys($record);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $columns_sql = implode(',', $columns);
        $stmt = $pdo->prepare("INSERT INTO registros ($columns_sql) VALUES ($placeholders)");
        $stmt->execute(array_values($record));
        $lastId = $pdo->lastInsertId();

        respond_and_exit(true, 'Registro guardado correctamente (ID ' . $lastId . ')');
    }

    // Si no hay método de almacenamiento
    throw new Exception('No hay método de almacenamiento disponible (ni archivo ni base de datos).');

} catch (Throwable $e) {
    @ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);

    // Guardar log del error
    $logDir = __DIR__ . '/data';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    file_put_contents($logDir . '/debug.log', date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, FILE_APPEND);
    exit;
}
 