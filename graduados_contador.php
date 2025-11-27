<?php
// dashboard_unificado.php
// ARCHIVO ÚNICO: total graduados, por programa y por hora.

// ---------- CONEXIÓN A LA BASE DE DATOS ----------
if (file_exists(__DIR__ . "/db.php")) {
    require_once __DIR__ . "/db.php"; // Debe crear $pdo
} else {
    // Configuración por variables de entorno (Render)
    $db_host = getenv("DB_HOST");
    $db_port = getenv("DB_PORT");
    $db_name = getenv("DB_NAME");
    $db_user = getenv("DB_USER");
    $db_pass = getenv("DB_PASS");

    try {
        $pdo = new PDO(
            "pgsql:host=$db_host;port=$db_port;dbname=$db_name",
            $db_user,
            $db_pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    } catch (Exception $e) {
        $pdo = null;
        $db_error = $e->getMessage();
    }
}

// ---------- SI PIDE JSON, DEVOLVER DATOS ----------
if (isset($_GET["json"])) {
    header("Content-Type: application/json");

    if (!$pdo) {
        echo json_encode(["success" => false, "error" => $db_error ?? "Sin conexión"]);
        exit;
    }

    try {
        // TOTAL DE GRADUADOS
        $total = $pdo->query("SELECT COUNT(*) FROM registros")->fetchColumn();

        // GRADUADOS POR PROGRAMA
        $por_programa = $pdo->query("
            SELECT programa, COUNT(*) AS total
            FROM registros
            WHERE programa IS NOT NULL AND programa <> ''
            GROUP BY programa
            ORDER BY total DESC
        ")->fetchAll();

        // GRADUADOS POR HORA
        $por_hora = $pdo->query("
            SELECT to_char(hora,'HH24:MI') AS hora, COUNT(*) AS total
            FROM registros
            WHERE hora IS NOT NULL
            GROUP BY hora
            ORDER BY hora ASC
        ")->fetchAll();

        echo json_encode([
            "success" => true,
            "total" => intval($total),
            "by_program" => $por_programa,
            "by_hour" => $por_hora
        ]);
        exit;

    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Unificado — Graduados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial; background:#f4f4f4; padding:20px; }
        .wrap { max-width:1100px; margin:auto; display:grid; grid-template-columns:300px 1fr; gap:20px; }
        .card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 10px #0002; }
        .big { font-size:48px; color:#0a8235; font-weight:bold; text-align:center; }
        table { width:100%; margin-top:10px; border-collapse:collapse; }
        th,td { padding:8px; border-bottom:1px solid #ddd; }
        th { background:#eee; }
        button { padding:8px 12px; border:none; background:#0a8235; color:white; border-radius:6px; cursor:pointer; }
        button.secondary { background:#444; }
        .controls { margin-top:15px; display:flex; gap:10px; }
        @media(max-width:800px){ .wrap{grid-template-columns:1fr;} }
    </style>
</head>
<body>

<div class="wrap">
    <div class="card">
        <h2>Total de Graduados</h2>
        <div id="total" class="big">0</div>

        <p>Última actualización: <span id="last">-</span></p>

        <div class="controls">
            <button onclick="loadData()">Actualizar</button>
            <button id="autoBtn" class="secondary">Auto: ON (5s)</button>
        </div>
    </div>

    <div class="card">
        <h2>Graduados por Programa</h2>
        <table id="tablaProgramas">
            <thead><tr><th>Programa</th><th>Total</th></tr></thead>
            <tbody><tr><td colspan="2">Cargando...</td></tr></tbody>
        </table>

        <h2 style="margin-top:20px;">Graduados por Hora</h2>
        <table id="tablaHoras">
            <thead><tr><th>Hora</th><th>Total</th></tr></thead>
            <tbody><tr><td colspan="2">Cargando...</td></tr></tbody>
        </table>
    </div>
</div>

<script>
let auto = true;
let interval;

function loadData() {
    fetch("?json=1")
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;

            document.getElementById("total").innerText = data.total;
            document.getElementById("last").innerText =
                new Date().toLocaleTimeString();

            // Programas
            let p = data.by_program.map(r =>
                `<tr><td>${r.programa}</td><td>${r.total}</td></tr>`
            ).join("");
            document.querySelector("#tablaProgramas tbody").innerHTML = p;

            // Horas
            let h = data.by_hour.map(r =>
                `<tr><td>${r.hora}</td><td>${r.total}</td></tr>`
            ).join("");
            document.querySelector("#tablaHoras tbody").innerHTML = h;
        });
}

document.getElementById("autoBtn").onclick = () => {
    auto = !auto;
    document.getElementById("autoBtn").innerText =
        auto ? "Auto: ON (5s)" : "Auto: OFF";

    if (auto) startAuto();
    else clearInterval(interval);
};

function startAuto() {
    interval = setInterval(loadData, 5000);
}

loadData();
startAuto();
</script>

</body>
</html>


