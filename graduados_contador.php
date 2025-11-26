<?php
// dashboard_unificado.php
// Archivo único que muestra: total graduados, por programa y por hora.
// Intenta usar db.php si existe; si no, intenta conectar por variables de entorno.

// --- Conexión a la DB ---
$pdo = null;
if (file_exists(__DIR__ . '/db.php')) {
    // Si tu db.php crea $pdo, perfecto.
    require_once __DIR__ . '/db.php';
} else {
    // Si no tienes db.php, edita estas variables o configura las env vars:
    $db_host = getenv('DB_HOST') ?: 'localhost';
    $db_port = getenv('DB_PORT') ?: '5432';
    $db_name = getenv('DB_NAME') ?: 'tu_base_de_datos';
    $db_user = getenv('DB_USER') ?: 'tu_usuario';
    $db_pass = getenv('DB_PASS') ?: 'tu_contraseña';

    try {
        // Asumimos PostgreSQL (tu tabla usa SERIAL).
        $dsn = "pgsql:host={$db_host};port={$db_port};dbname={$db_name}";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Exception $e) {
        // Si no hay conexión, y tampoco db.php, devolvemos error si piden JSON
        if (isset($_GET['json'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No se pudo conectar a la base de datos: '.$e->getMessage()]);
            exit;
        }
        $pdo = null;
        $db_error_msg = $e->getMessage();
    }
}

// --- Si se pidió JSON, devolvemos los datos crudos ---
if (isset($_GET['json'])) {
    header('Content-Type: application/json; charset=utf-8');

    if (!$pdo) {
        echo json_encode([
            'success' => false,
            'error' => isset($db_error_msg) ? $db_error_msg : 'Sin conexión a la base de datos.'
        ]);
        exit;
    }

    try {
        // 1) Total graduados
        $stmt = $pdo->query("SELECT COUNT(*)::int AS total FROM registros");
        $total = (int) $stmt->fetchColumn();

        // 2) Por programa (ignora nulos/vacíos)
        $sql_programa = "SELECT programa, COUNT(*)::int AS total
                         FROM registros
                         WHERE COALESCE(programa, '') <> ''
                         GROUP BY programa
                         ORDER BY total DESC, programa ASC";
        $stmt = $pdo->query($sql_programa);
        $por_programa = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3) Por hora (mostramos formato HH:MM). Asumimos Postgres; usar TO_CHAR.
        // Si usas MySQL, reemplaza por TIME_FORMAT(hora, '%H:%i') AS hora
        $sql_hora = "SELECT to_char(hora, 'HH24:MI') AS hora, COUNT(*)::int AS total
                     FROM registros
                     WHERE hora IS NOT NULL
                     GROUP BY hora
                     ORDER BY hora";
        $stmt = $pdo->query($sql_hora);
        $por_hora = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'total' => $total,
            'by_program' => $por_programa,
            'by_hour' => $por_hora
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Dashboard Unificado — Graduados</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:20px; }
        .wrap { max-width:1100px; margin:0 auto; display: grid; grid-template-columns: 320px 1fr; gap:20px; }
        .card { background:#fff; border-radius:10px; padding:18px; box-shadow:0 4px 18px rgba(0,0,0,0.06); }
        .big { font-size:48px; font-weight:700; color:#0b6b2f; text-align:center; }
        h2 { margin:0 0 8px 0; font-size:18px; }
        .table-card { grid-column: 2 / 3; }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        table th, table td { text-align:left; padding:8px 6px; border-bottom:1px solid #eee; }
        .small { font-size:14px; color:#666; }
        .controls { display:flex; gap:8px; margin-bottom:10px; }
        button { padding:8px 12px; border-radius:6px; border:none; cursor:pointer; background:#0b6b2f; color:white; }
        button.secondary { background:#666; }
        @media (max-width:800px) {
            .wrap { grid-template-columns: 1fr; }
            .table-card { grid-column: 1 / -1; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h2>Total de Graduados</h2>
            <div id="totalGraduados" class="big">0</div>

            <div class="small" style="margin-top:12px">
                Última actualización: <span id="lastUpdate">-</span>
            </div>

            <div style="margin-top:12px" class="controls">
                <button id="btnRefresh">Actualizar ahora</button>
                <button id="btnAuto" class="secondary">Auto: ON (5s)</button>
            </div>
        </div>

        <div class="card table-card">
            <h2>Graduados por Programa</h2>
            <div class="small">Ordenado por mayor cantidad</div>
            <table id="tablaProgramas" aria-live="polite">
                <thead>
                    <tr><th>Programa</th><th style="width:100px">Cantidad</th></tr>
                </thead>
                <tbody>
                    <tr><td colspan="2" class="small">Cargando...</td></tr>
                </tbody>
            </table>

            <h2 style="margin-top:18px">Graduados por Hora</h2>
            <div class="small">Formato HH:MM</div>
            <table id="tablaHoras" aria-live="polite">
                <thead>
                    <tr><th>Hora</th><th style="width:100px">Cantidad</th></tr>
                </thead>
                <tbody>
                    <tr><td colspan="2" class="small">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

<script>
const endpoint = location.pathname + '?json=1';
let auto = true;
let intervalId = null;

function renderData(data) {
    if (!data.success) {
        console.error('Error al obtener datos', data.error || 'unknown');
        document.getElementById('totalGraduados').innerText = 'Error';
        return;
    }

    // Total
    document.getElementById('totalGraduados').innerText = data.total;

    // Programas
    const tbodyP = document.querySelector('#tablaProgramas tbody');
    if (data.by_program.length === 0) {
        tbodyP.innerHTML = '<tr><td colspan="2" class="small">No hay registros.</td></tr>';
    } else {
        tbodyP.innerHTML = data.by_program.map(r =>
            `<tr><td>${escapeHtml(r.programa)}</td><td>${r.total}</td></tr>`
        ).join('');
    }

    // Horas
    const tbodyH = document.querySelector('#tablaHoras tbody');
    if (data.by_hour.length === 0) {
        tbodyH.innerHTML = '<tr><td colspan="2" class="small">No hay registros.</td></tr>';
    } else {
        tbodyH.innerHTML = data.by_hour.map(r =>
            `<tr><td>${escapeHtml(r.hora)}</td><td>${r.total}</td></tr>`
        ).join('');
    }

    // Last update
    const now = new Date();
    document.getElementById('lastUpdate').innerText = now.toLocaleString();
}

function fetchAndRender() {
    fetch(endpoint, {cache: 'no-store'})
        .then(resp => resp.json())
        .then(json => renderData(json))
        .catch(err => {
            console.error('Fetch error:', err);
            document.getElementById('totalGraduados').innerText = 'Error';
        });
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text).replace(/[&<>"'`=\/]/g, function (s) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;',
            '`': '&#x60;',
            '=': '&#x3D;'
        })[s];
    });
}

document.getElementById('btnRefresh').addEventListener('click', fetchAndRender);
document.getElementById('btnAuto').addEventListener('click', () => {
    auto = !auto;
    document.getElementById('btnAuto').innerText = auto ? 'Auto: ON (5s)' : 'Auto: OFF';
    if (auto) startAuto(); else stopAuto();
});

function startAuto() {
    if (intervalId) clearInterval(intervalId);
    intervalId = setInterval(fetchAndRender, 5000);
}
function stopAuto() {
    if (intervalId) clearInterval(intervalId);
    intervalId = null;
}

// Inicial
fetchAndRender();
startAuto();
</script>
</body>
</html>

