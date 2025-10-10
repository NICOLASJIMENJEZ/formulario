<?php
header('Content-Type: application/json; charset=utf-8');

// Evita errores por salida no JSON válida
ob_start();

// Maneja errores como excepciones
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

require_once __DIR__ . '/db.php'; // Debe definir $USE_FILE_STORAGE y, si corresponde, $pdo

// Detectar si la petición viene por AJAX
function is_ajax_request() {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') return true;
    if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) return true;
    return false;
}

function respond_and_exit($success, $message) {
    $ajax = is_ajax_request();
    if ($ajax) {
        @ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
    // Redirigir a index con parámetros (URL-encode) usando ruta relativa
    @ob_end_clean();
    $type = $success ? 'success' : 'danger';
    $url = './index.php?msg=' . urlencode($message) . '&type=' . urlencode($type);
    header('Location: ' . $url);
    exit;
}

try {
    function post($key) {
        return isset($_POST[$key]) ? trim($_POST[$key]) : null;
    }

    // Campos obligatorios
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

    // Preparar datos en formato asociativo para poder usar tanto DB como archivo
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
        'invitado1_nombre' => $invitados[1]['nombre'],
        'invitado1_apellidos' => $invitados[1]['apellidos'],
        'invitado1_cc' => $invitados[1]['cc'],
        'invitado2_nombre' => $invitados[2]['nombre'],
        'invitado2_apellidos' => $invitados[2]['apellidos'],
        'invitado2_cc' => $invitados[2]['cc'],
        'invitado3_nombre' => $invitados[3]['nombre'],
        'invitado3_apellidos' => $invitados[3]['apellidos'],
        'invitado3_cc' => $invitados[3]['cc'],
        'fecha_hora' => date('Y-m-d H:i:s')
    ];

    // Si se configuró almacenamiento en archivo, guardamos en data/records.json
    if (isset($USE_FILE_STORAGE) && $USE_FILE_STORAGE === true) {
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);
        $file = $dataDir . '/records.json';

        // Leer archivo existente con bloqueo, agregar nuevo registro con ID incremental
        $fp = fopen($file, 'c+');
        if (!$fp) throw new Exception('No se puede abrir el archivo de datos.');
        flock($fp, LOCK_EX);
        // Asegurarse de leer desde el inicio
        rewind($fp);
        $contents = stream_get_contents($fp);
        $arr = [];
        if ($contents) {
            $decoded = json_decode($contents, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $arr = $decoded;
            } else {
                // contenido corrupto: reiniciamos el arreglo
                $arr = [];
            }
        }
        // Generar ID incremental (seguro aunque haya huecos)
        $lastId = 0;
        foreach ($arr as $existing) {
            if (isset($existing['id']) && is_numeric($existing['id'])) {
                $lastId = max($lastId, (int)$existing['id']);
            }
        }
        $newId = $lastId + 1;
        $record['id'] = $newId;
        $arr[] = $record;

        // Rewind and write
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

    respond_and_exit(true, 'Registro guardado correctamente (ID ' . $newId . ')');
    }

    // Si no se usa almacenamiento en archivo, intentamos usar PDO/MySQL (si $pdo está disponible)
    if (isset($pdo) && $pdo instanceof PDO) {
        // Construir consulta dinámica segura
        $columns = array_keys($record);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $columns_sql = implode(',', $columns);
        $stmt = $pdo->prepare("INSERT INTO registros ($columns_sql) VALUES ($placeholders)");
        $stmt->execute(array_values($record));
        $lastId = $pdo->lastInsertId();
    respond_and_exit(true, 'Registro guardado correctamente (ID ' . $lastId . ')');
    }

    // Si llegamos aquí, no tenemos método de persistencia disponible
    throw new Exception('No hay método de almacenamiento disponible (ni archivo ni DB).');

} catch (Throwable $e) {
    @ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
    if (isset($stmt) && $stmt instanceof mysqli_stmt) @$stmt->close();
    if (isset($mysqli) && $mysqli instanceof mysqli) @$mysqli->close();
    if (isset($fp) && is_resource($fp)) {
        @flock($fp, LOCK_UN);
        @fclose($fp);
    }
    exit;
}
