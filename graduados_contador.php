<?php
// dashboard_unificado.php

// ---------- CONEXIÓN ----------
if (file_exists(__DIR__ . "/db.php")) {
    require_once __DIR__ . "/db.php";
} else {
    try {
        $pdo = new PDO(
            "pgsql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT") . ";dbname=" . getenv("DB_NAME"),
            getenv("DB_USER"),
            getenv("DB_PASS"),
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

// ---------- JSON ----------
if (isset($_GET["json"])) {
    header("Content-Type: application/json");

    if (!$pdo) {
        echo json_encode([
            "success" => false,
            "error" => $db_error ?? "Sin conexión"
        ]);
        exit;
    }

    try {
        // TOTAL GRADUADOS
        $total = $pdo->query("SELECT COUNT(*) FROM registros")->fetchColumn();

        <div class="controls">
    <button onclick="loadData()">Actualizar</button>
    <button id="autoBtn" class="secondary">Auto: ON (5s)</button>
</div>

<!-- ✅ BOTÓN NUEVO -->
<div style="margin-top:15px; text-align:center;">
    <a href="titulares.php">
        <button style="width:100%;">Ver Titulares</button>
    </a>
</div>


        // GRADUADOS POR PROGRAMA
        $por_programa = $pdo->query("
            SELECT programa, COUNT(*) AS total
            FROM registros
            WHERE programa IS NOT NULL AND programa <> ''
            GROUP BY programa
            ORDER BY total DESC
        ")->fetchAll();

        // GRADUADOS + INVITADOS POR HORA
        $por_hora = $pdo->query("
            SELECT
                to_char(hora,'HH24:MI') AS hora,
                COUNT(*) AS graduados,
                SUM(
                    (CASE WHEN invitado1_nombre <> '' THEN 1 ELSE 0 END) +
                    (CASE WHEN invitado2_nombre <> '' THEN 1 ELSE 0 END) +
                    (CASE WHEN invitado3_nombre <> '' THEN 1 ELSE 0 END)
                ) AS invitados
            FROM registros
            WHERE hora IS NOT NULL
            GROUP BY to_char(hora,'HH24:MI')
            ORDER BY hora
        ")->fetchAll();

        // ✅ INVITADOS POR PROGRAMA (NUEVO)
        $invitados_por_programa = $pdo->query("
            SELECT
                programa,
                SUM(
                    (CASE WHEN invitado1_nombre <> '' THEN 1 ELSE 0 END) +
                    (CASE WHEN invitado2_nombre <> '' THEN 1 ELSE 0 END) +
                    (CASE WHEN invitado3_nombre <> '' THEN 1 ELSE 0 END)
                ) AS invitados
            FROM registros
            WHERE programa IS NOT NULL AND programa <> ''
            GROUP BY programa
            ORDER BY invitados DESC
        ")->fetchAll();

        echo json_encode([
            "success" => true,
            "total" => (int)$total,
            "by_program" => $por_programa,
            "by_hour" => $por_hora,
            "by_program_guests" => $invitados_por_programa
        ]);
        exit;

    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "error" => $e->getMessage()
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard Graduados</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body { font-family: Arial; background:#f4f4f4; padding:20px; }
.wrap {
    max-width:1200px;
    margin:auto;
    display:grid;
    grid-template-columns:300px 1fr;
    gap:20px;
}
.card {
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 0 10px #0002;
}
.big {
    font-size:48px;
    color:#0a8235;
    font-weight:bold;
    text-align:center;
}
table {
    width:100%;
    margin-top:10px;
    border-collapse:collapse;
}
th, td {
    padding:8px;
    border-bottom:1px solid #ddd;
}
th { background:#eee; }
button {
    padding:8px 12px;
    border:none;
    background:#0a8235;
    color:white;
    border-radius:6px;
    cursor:pointer;
}
button.secondary { background:#444; }
.controls { margin-top:15px; display:flex; gap:10px; }
@media(max-width:900px){
    .wrap{grid-template-columns:1fr;}
}
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
<thead><tr><th>Programa</th><th>Graduados</th></tr></thead>
<tbody></tbody>
</table>

<h2 style="margin-top:20px;">Graduados e Invitados por Hora</h2>
<table id="tablaHoras">
<thead><tr><th>Hora</th><th>Graduados</th><th>Invitados</th></tr></thead>
<tbody></tbody>
</table>

<h2 style="margin-top:20px;">Invitados por Programa</h2>
<table id="tablaInvitadosPrograma">
<thead><tr><th>Programa</th><th>Total Invitados</th></tr></thead>
<tbody></tbody>
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
            if (!data.success) return alert(data.error);

            document.getElementById("total").innerText = data.total;
            document.getElementById("last").innerText =
                new Date().toLocaleTimeString();

            document.querySelector("#tablaProgramas tbody").innerHTML =
                data.by_program.map(r =>
                    `<tr><td>${r.programa}</td><td>${r.total}</td></tr>`
                ).join("");

            document.querySelector("#tablaHoras tbody").innerHTML =
                data.by_hour.map(r =>
                    `<tr><td>${r.hora}</td><td>${r.graduados}</td><td>${r.invitados}</td></tr>`
                ).join("");

            document.querySelector("#tablaInvitadosPrograma tbody").innerHTML =
                data.by_program_guests.map(r =>
                    `<tr><td>${r.programa}</td><td>${r.invitados}</td></tr>`
                ).join("");
        });
}

document.getElementById("autoBtn").onclick = () => {
    auto = !auto;
    document.getElementById("autoBtn").innerText =
        auto ? "Auto: ON (5s)" : "Auto: OFF";
    auto ? startAuto() : clearInterval(interval);
};

function startAuto() {
    interval = setInterval(loadData, 5000);
}

loadData();
startAuto();
</script>

</body>
</html>


